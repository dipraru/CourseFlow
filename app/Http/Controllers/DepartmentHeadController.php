<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CourseRegistration;
use App\Models\RegistrationApproval;
use App\Models\PaymentSlip;
use App\Models\Semester;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DepartmentHeadController extends Controller
{
    public function dashboard()
    {
        $currentSemester = Semester::where('is_current', true)->first();
        
        // Pending approvals count
        $pendingCount = CourseRegistration::where('status', 'advisor_approved')->count();
        
        // Stats
        $stats = [
            'pending_approvals' => $pendingCount,
            'approved_today' => CourseRegistration::where('status', 'head_approved')
                ->whereDate('head_approved_at', today())
                ->count(),
            'total_registrations' => CourseRegistration::whereHas('semester', function($q) {
                $q->where('is_current', true);
            })->count(),
            'payment_slips_generated' => PaymentSlip::whereHas('semester', function($q) {
                $q->where('is_current', true);
            })->count(),
        ];
        
        // Recent approvals
        $recentApprovals = CourseRegistration::with(['student.profile', 'semesterCourse.course', 'semester'])
            ->where('status', 'head_approved')
            ->latest('head_approved_at')
            ->take(10)
            ->get();
        
        return view('department-head.dashboard', compact('stats', 'recentApprovals', 'currentSemester'));
    }
    
    public function pendingApprovals()
    {
        $registrations = CourseRegistration::with(['student.profile.batch', 'semesterCourse.course', 'semester'])
            ->where('status', 'advisor_approved')
            ->latest()
            ->paginate(20);
        
        return view('department-head.pending-approvals', compact('registrations'));
    }
    
    public function approve(Request $request, CourseRegistration $registration)
    {
        $request->validate([
            'comments' => 'nullable|string|max:500',
        ]);
        
        if ($registration->status !== 'advisor_approved') {
            return back()->with('error', 'This registration cannot be approved at this stage.');
        }
        
        DB::beginTransaction();
        try {
            // Update registration status
            $now = now();
            $registration->update([
                'status' => 'head_approved',
                'head_approved_at' => $now,
                'dept_head_approved_at' => $now, // keep both timestamps in sync for compatibility
            ]);
            
            // Update department head approval record
            RegistrationApproval::where('course_registration_id', $registration->id)
                ->where('approver_role', 'department_head')
                ->update([
                    'status' => 'approved',
                    'comments' => $request->comments,
                    'action_taken_at' => $now,
                ]);
            
            // Generate payment slip for the student and semester
            $student = $registration->student;
            $semester = $registration->semester;
            
            // Check if payment slip already exists for this student and semester
            $existingSlip = PaymentSlip::where('student_id', $student->id)
                ->where('semester_id', $semester->id)
                ->first();

            // Get fee structure
            $fee = null;
            if (Schema::hasColumn('fees', 'batch_id') && !empty($student->profile->batch_id)) {
                // Try batch-specific fee first
                $fee = Fee::where('batch_id', $student->profile->batch_id)
                    ->where('semester_id', $semester->id)
                    ->first();
            }

            // Always fall back to a semester-level fee when batch-specific fee is not found
            if (! $fee) {
                $fee = Fee::where('semester_id', $semester->id)->first();
            }

            // Build current course info
            $course = $registration->semesterCourse->course ?? null;
            $courseEntry = null;
            $courseCredit = 0;
            if ($course) {
                $courseEntry = [
                    'id' => $course->id,
                    'code' => $course->course_code ?? null,
                    'name' => $course->course_name ?? null,
                    'credit_hours' => (float)($course->credit_hours ?? 0),
                ];
                $courseCredit = (float)($course->credit_hours ?? 0);
            }

            if ($fee) {
                // If there's an existing slip, append the course (if missing) and recalc totals.
                if ($existingSlip) {
                    $registered = $existingSlip->registered_courses ?? [];

                    // append if not already present
                    $found = false;
                    if ($courseEntry) {
                        foreach ($registered as $rc) {
                            if (isset($rc['id']) && $rc['id'] == $courseEntry['id']) {
                                $found = true;
                                break;
                            }
                        }
                        if (! $found) {
                            $registered[] = $courseEntry;
                        }
                    }

                    // Recalculate credit hours and totals
                    $totalCreditHours = array_sum(array_map(function ($c) { return (float)($c['credit_hours'] ?? 0); }, $registered));
                    $perCredit = (float)($fee->per_credit_fee ?? 0);
                    $otherFees = (float)($fee->admission_fee ?? 0) + (float)($fee->library_fee ?? 0) + (float)($fee->lab_fee ?? 0) + (float)($fee->other_fees ?? 0);
                    $newTotal = $perCredit * $totalCreditHours + $otherFees;

                    $existingSlip->update([
                        'registered_courses' => $registered,
                        'credit_hours' => $totalCreditHours,
                        'total_amount' => $newTotal,
                        'fee_breakdown' => array_merge($existingSlip->fee_breakdown ?? [], [
                            'per_credit_fee' => $perCredit,
                            'credit_hours' => $totalCreditHours,
                            'calculated_total' => $newTotal,
                        ]),
                    ]);

                } else {
                    // No existing slip: create a new one for this student/semester. Use computed credit hours and fee breakdown.
                    $registeredCourses = [];
                    if ($courseEntry) {
                        $registeredCourses[] = $courseEntry;
                    }

                    $creditHours = $courseCredit;
                    $totalAmount = $perCredit = (float)($fee->per_credit_fee ?? 0) * (float)$creditHours
                        + (float)($fee->admission_fee ?? 0)
                        + (float)($fee->library_fee ?? 0)
                        + (float)($fee->lab_fee ?? 0)
                        + (float)($fee->other_fees ?? 0);

                    $feeBreakdown = [
                        'per_credit_fee' => (float)($fee->per_credit_fee ?? 0),
                        'credit_hours' => (float)$creditHours,
                        'admission_fee' => (float)($fee->admission_fee ?? 0),
                        'library_fee' => (float)($fee->library_fee ?? 0),
                        'lab_fee' => (float)($fee->lab_fee ?? 0),
                        'other_fees' => (float)($fee->other_fees ?? 0),
                        'calculated_total' => (float)$totalAmount,
                    ];

                    $batchName = optional(optional($student->profile)->batch)->name ?? 'BATCH';
                    $slipNumber = strtoupper($semester->name) . '-' . $batchName . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);

                    PaymentSlip::create([
                        'student_id' => $student->id,
                        'semester_id' => $semester->id,
                        'slip_number' => $slipNumber,
                        'total_amount' => $totalAmount,
                        'credit_hours' => $creditHours,
                        'fee_breakdown' => $feeBreakdown,
                        'registered_courses' => $registeredCourses,
                        'payment_status' => 'unpaid',
                        'generated_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            
            return back()->with('success', 'Registration approved and payment slip generated.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve registration: ' . $e->getMessage());
        }
    }
    
    public function reject(Request $request, CourseRegistration $registration)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        if ($registration->status !== 'advisor_approved') {
            return back()->with('error', 'This registration cannot be rejected at this stage.');
        }
        
        DB::beginTransaction();
        try {
            // Update registration status
            $registration->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
            ]);
            
            // Update department head approval record
            RegistrationApproval::where('course_registration_id', $registration->id)
                ->where('approver_role', 'department_head')
                ->update([
                    'status' => 'rejected',
                    'comments' => $request->rejection_reason,
                    'action_taken_at' => now(),
                ]);
            
            DB::commit();
            
            return back()->with('success', 'Registration rejected.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject registration.');
        }
    }
    
    public function statistics()
    {
        $currentSemester = Semester::where('is_current', true)->first();
        
        // Registration statistics by status
        $statusStats = CourseRegistration::whereHas('semester', function($q) {
                $q->where('is_current', true);
            })
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        // Payment statistics
        $paymentStats = PaymentSlip::whereHas('semester', function($q) {
                $q->where('is_current', true);
            })
            ->select('payment_status', DB::raw('count(*) as count'))
            ->groupBy('payment_status')
            ->get();
        
        // Batch-wise statistics
        $batchStats = User::where('role', 'student')
            ->with(['profile.batch', 'courseRegistrations' => function($q) {
                $q->whereHas('semester', function($sq) {
                    $sq->where('is_current', true);
                });
            }])
            ->get()
            ->groupBy('profile.batch.name');
        
        return view('department-head.statistics', compact('statusStats', 'paymentStats', 'batchStats', 'currentSemester'));
    }
}

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
            'approved_today' => CourseRegistration::where('status', 'approved')
                ->whereDate('dept_head_approved_at', today())
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
            ->where('status', 'approved')
            ->latest('dept_head_approved_at')
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
            $registration->update([
                'status' => 'approved',
                'dept_head_approved_at' => now(),
            ]);
            
            // Update department head approval record
            RegistrationApproval::where('course_registration_id', $registration->id)
                ->where('approver_role', 'department_head')
                ->update([
                    'status' => 'approved',
                    'comments' => $request->comments,
                    'action_taken_at' => now(),
                ]);
            
            // Generate payment slip for the student and semester
            $student = $registration->student;
            $semester = $registration->semester;
            
            // Check if payment slip already exists for this student and semester
            $existingSlip = PaymentSlip::where('student_id', $student->id)
                ->where('semester_id', $semester->id)
                ->first();
            
            if (!$existingSlip) {
                // Get fee structure
                $fee = Fee::where('batch_id', $student->profile->batch_id)
                    ->where('semester_id', $semester->id)
                    ->first();
                
                if ($fee) {
                    // Generate slip number (format: SEMESTER_YEAR-BATCH-STUDENT_ID)
                    $slipNumber = strtoupper($semester->name) . '-' . 
                                 $student->profile->batch->name . '-' . 
                                 str_pad($student->id, 4, '0', STR_PAD_LEFT);
                    
                    PaymentSlip::create([
                        'student_id' => $student->id,
                        'semester_id' => $semester->id,
                        'slip_number' => $slipNumber,
                        'total_amount' => $fee->total_amount,
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

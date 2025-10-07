<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CourseRegistration;
use App\Models\RegistrationApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvisorController extends Controller
{
    public function dashboard()
    {
        $advisor = auth()->user();
        
        // Get advisor's students
        $studentCount = User::where('role', 'student')
            ->whereHas('profile', function($q) use ($advisor) {
                $q->where('advisor_id', $advisor->id);
            })->count();
        
        // Get pending registrations count
        $pendingCount = CourseRegistration::whereHas('student.profile', function($q) use ($advisor) {
                $q->where('advisor_id', $advisor->id);
            })
            ->where('status', 'pending')
            ->count();
        
        // Recent registrations
        $recentRegistrations = CourseRegistration::with(['student', 'semesterCourse.course', 'semester'])
            ->whereHas('student.profile', function($q) use ($advisor) {
                $q->where('advisor_id', $advisor->id);
            })
            ->latest()
            ->take(10)
            ->get();
        
        $stats = [
            'students' => $studentCount,
            'pending' => $pendingCount,
            'approved_today' => CourseRegistration::whereHas('student.profile', function($q) use ($advisor) {
                    $q->where('advisor_id', $advisor->id);
                })
                ->where('status', 'advisor_approved')
                ->whereDate('advisor_approved_at', today())
                ->count(),
        ];
        
        return view('advisor.dashboard', compact('advisor', 'stats', 'recentRegistrations'));
    }
    
    public function students()
    {
        $advisor = auth()->user();
        
        $students = User::with(['profile.batch', 'courseRegistrations'])
            ->where('role', 'student')
            ->whereHas('profile', function($q) use ($advisor) {
                $q->where('advisor_id', $advisor->id);
            })
            ->paginate(20);
        
        return view('advisor.students', compact('students'));
    }
    
    public function pendingRegistrations()
    {
        $advisor = auth()->user();
        
        $registrations = CourseRegistration::with(['student.profile', 'semesterCourse.course', 'semester'])
            ->whereHas('student.profile', function($q) use ($advisor) {
                $q->where('advisor_id', $advisor->id);
            })
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);
        
        return view('advisor.pending-registrations', compact('registrations'));
    }
    
    public function approve(Request $request, CourseRegistration $registration)
    {
        $request->validate([
            'comments' => 'nullable|string|max:500',
        ]);
        
        $advisor = auth()->user();
        
        // Verify this registration belongs to advisor's student
        if ($registration->student->profile->advisor_id !== $advisor->id) {
            abort(403, 'Unauthorized');
        }
        
        DB::beginTransaction();
        try {
            // Update registration status
            $registration->update([
                'status' => 'advisor_approved',
                'advisor_approved_at' => now(),
            ]);
            
            // Update advisor approval record
            RegistrationApproval::where('course_registration_id', $registration->id)
                ->where('approver_role', 'advisor')
                ->update([
                    'status' => 'approved',
                    'comments' => $request->comments,
                    'action_taken_at' => now(),
                ]);
            
            // Create approval record for department head
            $departmentHead = User::where('role', 'department_head')->first();
            if ($departmentHead) {
                RegistrationApproval::create([
                    'course_registration_id' => $registration->id,
                    'approver_id' => $departmentHead->id,
                    'approver_role' => 'department_head',
                    'status' => 'pending',
                ]);
            }
            
            DB::commit();
            
            return back()->with('success', 'Registration approved successfully.');
            
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
        
        $advisor = auth()->user();
        
        // Verify this registration belongs to advisor's student
        if ($registration->student->profile->advisor_id !== $advisor->id) {
            abort(403, 'Unauthorized');
        }
        
        DB::beginTransaction();
        try {
            // Update registration status
            $registration->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
            ]);
            
            // Update advisor approval record
            RegistrationApproval::where('course_registration_id', $registration->id)
                ->where('approver_role', 'advisor')
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
}

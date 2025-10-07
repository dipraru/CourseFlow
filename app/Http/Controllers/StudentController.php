<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\SemesterCourse;
use App\Models\CourseRegistration;
use App\Models\PaymentSlip;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = auth()->user();
        
        // Get current semester
        // Prefer semesters where today falls within the registration window
        $today = now()->toDateString();
        $currentSemester = Semester::whereDate('registration_start_date', '<=', $today)
            ->whereDate('registration_end_date', '>=', $today)
            ->first();

        // Fallback to any active semester (legacy behavior)
        if (! $currentSemester) {
            $currentSemester = Semester::where('is_active', true)->first();
        }

        // Provide legacy-friendly property names used in blades
        if ($currentSemester) {
            $currentSemester->registration_start = $currentSemester->registration_start_date;
            $currentSemester->registration_end = $currentSemester->registration_end_date;
        }
        
        // Get student's registrations for current semester (per-course)
        $registrations = CourseRegistration::with(['semesterCourse.course', 'semester'])
            ->where('student_id', $student->id)
            ->where('semester_id', $currentSemester?->id)
            ->latest()
            ->get();

        // Compute application-level status for current semester (treat all course registrations for a student+semester as one application)
        $hasAny = $registrations->isNotEmpty();
        $hasHeadApproved = $registrations->contains(function ($r) {
            return in_array($r->status, ['head_approved', 'completed']);
        });
        $hasAdvisorApproved = $registrations->contains(function ($r) {
            return $r->status === 'advisor_approved';
        });
        $hasRejected = $registrations->contains(function ($r) {
            return $r->status === 'rejected';
        });

        // Derive counts: one application per student per semester
        $pending = ($hasAny && ! $hasHeadApproved && ! $hasRejected) ? 1 : 0; // advisor approvals are still pending overall
        $advisorApproved = ($hasAny && $hasAdvisorApproved && ! $hasHeadApproved) ? 1 : 0; // intermediate state
        $headApproved = $hasHeadApproved ? 1 : 0;
        $completed = $registrations->contains(function ($r) { return $r->status === 'completed'; }) ? 1 : 0;
        $rejected = ($hasAny && $hasRejected && ! $hasHeadApproved) ? 1 : 0;

        $approved = $headApproved + $completed; // final approvals only
        $total = $hasAny ? 1 : 0;

        $stats = [
            'pending' => $pending,
            'advisor_approved' => $advisorApproved,
            'head_approved' => $headApproved,
            'completed' => $completed,
            'rejected' => $rejected,
            'approved' => $approved,
            'total' => $total,
        ];
        
        // Get payment slips
        $paymentSlips = PaymentSlip::where('student_id', $student->id)
            ->latest()
            ->take(5)
            ->get();
        
        return view('student.dashboard', compact('student', 'currentSemester', 'registrations', 'stats', 'paymentSlips'));
    }
    
    public function courses()
    {
        $student = auth()->user();
        
        // Get active semester
        // Prefer registration-window based semester
        $today = now()->toDateString();
        $activeSemester = Semester::whereDate('registration_start_date', '<=', $today)
            ->whereDate('registration_end_date', '>=', $today)
            ->first();

        if (! $activeSemester) {
            $activeSemester = Semester::where('is_active', true)->first();
        }
        
        if (!$activeSemester) {
            return redirect()->route('student.dashboard')
                ->with('error', 'No active semester available for registration.');
        }
        
    // Get available courses for the semester
        $availableCourses = SemesterCourse::with('course')
            ->where('semester_id', $activeSemester->id)
            ->where('is_available', true)
            ->get();
        
        // Get student's already registered courses for this semester
        $registeredCourseIds = CourseRegistration::where('student_id', $student->id)
            ->where('semester_id', $activeSemester->id)
            ->whereIn('status', ['pending', 'advisor_approved', 'head_approved', 'completed'])
            ->pluck('semester_course_id')
            ->toArray();
        
    // Provide $currentSemester for the view (legacy var name used in blade)
    $currentSemester = $activeSemester;
    if ($currentSemester) {
        $currentSemester->registration_start = $currentSemester->registration_start_date;
        $currentSemester->registration_end = $currentSemester->registration_end_date;
    }

    return view('student.courses', compact('activeSemester', 'availableCourses', 'registeredCourseIds', 'currentSemester'));
    }
    
    public function registrations()
    {
        $student = auth()->user();
        
        $registrations = CourseRegistration::with(['semesterCourse.course', 'semester', 'approvals.approver'])
            ->where('student_id', $student->id)
            ->latest()
            ->paginate(15);
        
        return view('student.registrations', compact('registrations'));
    }
}

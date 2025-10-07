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
        $currentSemester = Semester::where('is_active', true)->first();
        
        // Get student's registrations for current semester
        $registrations = CourseRegistration::with(['semesterCourse.course', 'semester'])
            ->where('student_id', $student->id)
            ->where('semester_id', $currentSemester?->id)
            ->latest()
            ->get();
        
        // Count registrations by status
        $stats = [
            'pending' => CourseRegistration::where('student_id', $student->id)->where('status', 'pending')->count(),
            'advisor_approved' => CourseRegistration::where('student_id', $student->id)->where('status', 'advisor_approved')->count(),
            'head_approved' => CourseRegistration::where('student_id', $student->id)->where('status', 'head_approved')->count(),
            'completed' => CourseRegistration::where('student_id', $student->id)->where('status', 'completed')->count(),
            'rejected' => CourseRegistration::where('student_id', $student->id)->where('status', 'rejected')->count(),
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
        $activeSemester = Semester::where('is_active', true)->first();
        
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
        
        return view('student.courses', compact('activeSemester', 'availableCourses', 'registeredCourseIds'));
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

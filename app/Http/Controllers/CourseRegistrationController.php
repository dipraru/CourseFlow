<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\SemesterCourse;
use App\Models\CourseRegistration;
use App\Models\RegistrationApproval;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'semester_courses' => 'required|array|min:1',
            'semester_courses.*' => 'exists:semester_courses,id',
            'remarks' => 'nullable|string|max:500',
        ]);
        
        $student = auth()->user();
        $activeSemester = Semester::where('is_active', true)->firstOrFail();
        
        DB::beginTransaction();
        try {
            $registeredCount = 0;
            
            foreach ($request->semester_courses as $semesterCourseId) {
                // Check if already registered
                $exists = CourseRegistration::where('student_id', $student->id)
                    ->where('semester_id', $activeSemester->id)
                    ->where('semester_course_id', $semesterCourseId)
                    ->whereIn('status', ['pending', 'advisor_approved', 'head_approved', 'completed'])
                    ->exists();
                
                if ($exists) {
                    continue;
                }
                
                // Get semester course details
                $semesterCourse = SemesterCourse::with('course')->findOrFail($semesterCourseId);
                
                // Create registration
                $registration = CourseRegistration::create([
                    'student_id' => $student->id,
                    'semester_id' => $activeSemester->id,
                    'semester_course_id' => $semesterCourseId,
                    'status' => 'pending',
                    'student_remarks' => $request->remarks,
                    'applied_at' => now(),
                ]);
                
                // Create approval record for advisor if advisor exists on student's profile
                if ($student->profile && $student->profile->advisor_id) {
                    RegistrationApproval::create([
                        'course_registration_id' => $registration->id,
                        'approver_id' => $student->profile->advisor_id,
                        'approver_role' => 'advisor',
                        'status' => 'pending',
                    ]);
                } else {
                    // If no advisor assigned, log a warning and continue (admins can handle approvals)
                    \Log::warning('Student missing advisor; skipping advisor approval creation', [
                        'student_id' => $student->id,
                        'registration_id' => $registration->id,
                    ]);
                }
                
                $registeredCount++;
            }
            
            DB::commit();
            
            return redirect()->route('student.registrations')
                ->with('success', "Successfully applied for {$registeredCount} course(s). Waiting for advisor approval.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            // Log exception with full trace for debugging
            \Log::error('Course registration failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $student->id ?? null,
            ]);
            return back()->with('error', 'Failed to register courses. Please try again.');
        }
    }
}

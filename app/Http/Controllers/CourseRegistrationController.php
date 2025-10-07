<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\SemesterCourse;
use App\Models\CourseRegistration;
use App\Models\RegistrationApproval;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CourseRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'semester_courses' => 'required|array|min:1',
            'semester_courses.*' => 'exists:semester_courses,id',
            'remarks' => 'nullable|string|max:500',
        ]);
        
    $student = Auth::user();
        // Determine active semester by registration window first, then fallback to is_active
        $today = now()->toDateString();
        $activeSemester = Semester::whereDate('registration_start_date', '<=', $today)
            ->whereDate('registration_end_date', '>=', $today)
            ->first();

        if (! $activeSemester) {
            $activeSemester = Semester::where('is_active', true)->firstOrFail();
        }
        
        DB::beginTransaction();
        try {
            $registeredCount = 0;
            // If student already has a final-approved application for this semester, block further registrations
            $finalExists = CourseRegistration::where('student_id', $student->id)
                ->where('semester_id', $activeSemester->id)
                ->whereIn('status', ['head_approved', 'completed'])
                ->exists();

            if ($finalExists) {
                DB::rollBack();
                return back()->with('error', 'Your registration for this semester has been finally approved. You cannot submit new registrations for this semester.');
            }

            // If there are existing non-final registrations for this student+semester, remove them (and their approvals) before creating a new application
            $existingRegs = CourseRegistration::where('student_id', $student->id)
                ->where('semester_id', $activeSemester->id)
                ->whereIn('status', ['pending', 'advisor_approved', 'rejected'])
                ->get();

            if ($existingRegs->isNotEmpty()) {
                $existingIds = $existingRegs->pluck('id')->toArray();
                // delete related approvals
                \App\Models\RegistrationApproval::whereIn('course_registration_id', $existingIds)->delete();
                // delete registrations
                CourseRegistration::whereIn('id', $existingIds)->delete();
            }

            foreach ($request->semester_courses as $semesterCourseId) {
                // Get semester course details
                $semesterCourse = SemesterCourse::with('course')->findOrFail($semesterCourseId);

                // Ensure the semester_course belongs to the active semester
                if ($semesterCourse->semester_id !== $activeSemester->id) {
                    // Skip and log if the selected semester_course is not for the active semester
                    Log::warning('Attempt to register for semester_course not in active semester', [
                        'student_id' => $student->id,
                        'semester_course_id' => $semesterCourseId,
                        'active_semester_id' => $activeSemester->id,
                        'semester_course_semester_id' => $semesterCourse->semester_id,
                    ]);
                    continue;
                }

                // Check if already registered
                $exists = CourseRegistration::where('student_id', $student->id)
                    ->where('semester_id', $semesterCourse->semester_id)
                    ->where('semester_course_id', $semesterCourseId)
                    ->whereIn('status', ['pending', 'advisor_approved', 'head_approved', 'completed'])
                    ->exists();
                
                if ($exists) {
                    continue;
                }
                
                // Create registration using semester_id from the semesterCourse
                $registration = CourseRegistration::create([
                    'student_id' => $student->id,
                    'semester_id' => $semesterCourse->semester_id,
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
                    Log::warning('Student missing advisor; skipping advisor approval creation', [
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
            Log::error('Course registration failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $student->id ?? null,
            ]);
            return back()->with('error', 'Failed to register courses. Please try again.');
        }
    }
}

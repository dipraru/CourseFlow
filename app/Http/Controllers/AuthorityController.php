<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Semester;
use App\Models\Course;
use App\Models\SemesterCourse;
use App\Models\CourseRegistration;
use App\Models\Fee;
use App\Models\PaymentSlip;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthorityController extends Controller
{
    public function dashboard()
    {
        $currentSemester = Semester::where('is_current', true)->first();
        
        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'total_courses' => Course::count(),
            'active_semester' => $currentSemester ? $currentSemester->name : 'None',
            'pending_payments' => PaymentSlip::where('payment_status', 'unpaid')->count(),
            'verified_payments' => PaymentSlip::where('payment_status', 'verified')->count(),
        ];
        
        // Recent payment slips approved by authority (verified)
        $recentPayments = PaymentSlip::with(['student.profile', 'semester', 'verifiedBy'])
            ->where('payment_status', 'verified')
            ->latest('verified_at')
            ->take(10)
            ->get();

        // Pending payment slips: students who received slip but haven't submitted payment
    $pendingPayments = PaymentSlip::with(['student.profile', 'semester', 'verifiedBy'])
            ->where('payment_status', 'unpaid')
            ->latest()
            ->take(20)
            ->get();
        
        return view('authority.dashboard', compact('stats', 'recentPayments', 'currentSemester', 'pendingPayments'));
    }

    /**
     * Approve a pending payment slip (mark as verified).
     */
    public function approvePayment(Request $request, PaymentSlip $paymentSlip)
    {
        // Only allow approving slips that are in 'paid' or 'unpaid' state that need authority action.
        if (! in_array($paymentSlip->payment_status, ['paid', 'unpaid'])) {
            return back()->with('error', 'This payment slip cannot be approved.');
        }

        $paymentSlip->update([
            'payment_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        return back()->with('success', 'Payment slip verified successfully.');
    }

    /**
     * Reject a pending payment slip (mark as unpaid and add remarks).
     */
    public function rejectPayment(Request $request, PaymentSlip $paymentSlip)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        // Only allow rejecting slips that are awaiting verification
        if (! in_array($paymentSlip->payment_status, ['paid', 'unpaid'])) {
            return back()->with('error', 'This payment slip cannot be rejected.');
        }

        $paymentSlip->update([
            'payment_status' => 'unpaid',
            'payment_remarks' => $request->input('rejection_reason'),
        ]);

        return back()->with('success', 'Payment slip rejected and student notified.');
    }
    
    // Semester Management
    public function semesters()
    {
        $semesters = Semester::select('semesters.*', DB::raw('(select count(distinct student_id) from course_registrations where course_registrations.semester_id = semesters.id) as student_registrations_count'))
            ->withCount(['semesterCourses'])
            ->latest()
            ->paginate(15);
        return view('authority.semesters.index', compact('semesters'));
    }

    public function createSemester()
    {
        return view('authority.semesters.create');
    }

    public function storeSemester(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:semesters',
            'type' => 'required|in:Spring,Summer,Fall',
            'year' => 'required|integer|min:2020|max:2050',
            // semester_number is determined server-side
            'batch_year' => 'nullable|integer|min:2000|max:2100|exists:batches,year',
            'registration_start_date' => 'required|date',
            'registration_end_date' => 'required|date|after:registration_start_date',
            'semester_start_date' => 'required|date',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id',
        ]);

        // Prevent opening a duplicate semester for the same batch/year/number
        $existingQuery = Semester::where('semester_number', $request->input('semester_number'))
            ->where('year', $request->input('year'));
        if ($request->filled('batch_year')) {
            $batch = Batch::where('year', $request->input('batch_year'))->first();
            $batchId = $batch?->id;
            if (is_null($batchId)) {
                // no such batch — validation should have prevented this, but guard anyway
                return back()->withInput()->with('error', 'Selected batch year not found.');
            }
            $existingQuery->where('batch_id', $batchId);
        } else {
            $existingQuery->whereNull('batch_id');
        }
        if ($existingQuery->exists()) {
            return back()->withInput()->with('error', 'A semester with the same year and number already exists for this batch.');
        }

        DB::beginTransaction();
        try {
            // Resolve batch_id from optional batch_year input
            $batchId = null;
            if ($request->filled('batch_year')) {
                $batch = Batch::where('year', $request->input('batch_year'))->first();
                if ($batch) {
                    $batchId = $batch->id;
                }
            }

            // Map request inputs to model columns
            $data = $request->only([
                'name', 'type', 'year',
                'registration_start_date', 'registration_end_date',
                'semester_start_date', 'semester_end_date'
            ]);
            // Determine semester_number automatically based on batch history
            // If no batch is provided, use 0 as requested
            if ($batchId) {
                $last = Semester::where('batch_id', $batchId)->orderBy('semester_number', 'desc')->first();
                $nextNumber = $last ? ($last->semester_number + 1) : 1;
            } else {
                // No batch provided -> semester number should be 0
                $nextNumber = 0;
            }
            $data['semester_number'] = $nextNumber;
            // set batch_id if resolved
            $data['batch_id'] = $batchId;

            // New behavior: created semester is automatically active and marked current for its batch
            $data['is_active'] = true;
            $data['is_current'] = true;

            $createdSemester = Semester::create($data);

            // End the previous semester for the same batch (set its end date to day before new semester start)
            $previousQuery = Semester::where(function($q) use ($createdSemester) {
                if (is_null($createdSemester->batch_id)) {
                    $q->whereNull('batch_id');
                } else {
                    $q->where('batch_id', $createdSemester->batch_id);
                }
            })->where('id', '<>', $createdSemester->id)
              ->where('semester_number', '<', $createdSemester->semester_number)
              ->orderBy('semester_number', 'desc');
            $previous = $previousQuery->first();
            if ($previous && !empty($createdSemester->semester_start_date)) {
                try {
                    $endDate = Carbon::parse($createdSemester->semester_start_date)->subDay()->toDateString();
                    $previous->update(['semester_end_date' => $endDate, 'is_active' => false, 'is_current' => false, 'is_locked' => true]);
                } catch (\Throwable $e) {
                    // ignore date parse errors
                }
            }

            // Deactivate and lock other semesters for the same batch (rest)
            Semester::where('id', '<>', $createdSemester->id)
                ->where(function($q) use ($createdSemester) {
                    if (is_null($createdSemester->batch_id)) {
                        $q->whereNull('batch_id');
                    } else {
                        $q->where('batch_id', $createdSemester->batch_id);
                    }
                })
                ->update(['is_active' => false, 'is_current' => false, 'is_locked' => true]);

            // Automatically attach courses whose intended_semester matches the selected semester_number
            // Use the created semester's computed semester_number as the intended semester
            $intended = (int) $createdSemester->semester_number;
            $coursesToAttach = Course::where('intended_semester', $intended)->pluck('id')->toArray();
            foreach ($coursesToAttach as $courseId) {
                SemesterCourse::create([
                    'semester_id' => $createdSemester->id,
                    'course_id' => $courseId,
                    'max_students' => 60,
                    'enrolled_students' => 0,
                    'is_available' => true,
                ]);
            }

                DB::commit();
                return redirect()->route('authority.semesters')->with('success', 'Semester created successfully.');

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Failed to create semester', ['exception' => $e->getMessage()]);
                $msg = app()->environment('local') ? $e->getMessage() : 'Failed to create semester.';
                return back()->with('error', $msg)->withInput();
            }
        }
    
    public function editSemester(Semester $semester)
    {
        $selected = $semester->semesterCourses()->pluck('course_id')->toArray();
        return view('authority.semesters.edit', compact('semester', 'selected'));
    }
    
    public function updateSemester(Request $request, Semester $semester)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:semesters,name,' . $semester->id,
            'type' => 'required|in:Spring,Summer,Fall',
            'year' => 'required|integer|min:2020|max:2050',
            'semester_number' => 'required|integer|min:1',
            'batch_year' => 'nullable|integer|min:2000|max:2100|exists:batches,year',
            'registration_start_date' => 'required|date',
            'registration_end_date' => 'required|date|after:registration_start_date',
            'semester_start_date' => 'required|date',
            'semester_end_date' => 'required|date|after:semester_start_date',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id',
        ]);

        // Prevent creating a duplicate semester when updating: same batch/year/number
        $existingQuery = Semester::where('semester_number', $request->input('semester_number'))
            ->where('year', $request->input('year'))
            ->where('id', '<>', $semester->id);
        if ($request->filled('batch_year')) {
            $batch = Batch::where('year', $request->input('batch_year'))->first();
            $batchId = $batch?->id;
            if (is_null($batchId)) {
                return back()->withInput()->with('error', 'Selected batch year not found.');
            }
            $existingQuery->where('batch_id', $batchId);
        } else {
            $existingQuery->whereNull('batch_id');
        }
        if ($existingQuery->exists()) {
            return back()->withInput()->with('error', 'A semester with the same year and number already exists for this batch.');
        }

        DB::beginTransaction();
        try {
            // Resolve batch by provided year (if any)
            $batchId = null;
            if ($request->filled('batch_year')) {
                $batch = Batch::where('year', $request->input('batch_year'))->first();
                if ($batch) {
                    $batchId = $batch->id;
                }
            }

            $data = $request->only([
                'name', 'type', 'year', 'semester_number',
                'registration_start_date', 'registration_end_date',
                'semester_start_date', 'semester_end_date'
            ]);
            $data['batch_id'] = $batchId;

            // If attempting to set is_active via update, ensure the semester is not locked
            if (array_key_exists('is_active', $data) && $data['is_active']) {
                if ($semester->is_locked) {
                    return back()->with('error', 'This semester was previously deactivated and cannot be reactivated.');
                }
            }

            // Auto-enable is_active if today's date falls within registration window
            try {
                $today = now()->toDateString();
                if (!empty($data['registration_start_date']) && !empty($data['registration_end_date'])) {
                    if ($data['registration_start_date'] <= $today && $today <= $data['registration_end_date']) {
                        $data['is_active'] = true;
                    }
                }
            } catch (\Throwable $e) {
                // ignore date parse errors
            }

            $semester->update($data);

            // If this semester is active after update, mark it as current and deactivate/lock other semesters for the same batch
            if (!empty($semester->is_active)) {
                // mark this semester as current
                $semester->update(['is_current' => true]);

                Semester::where('id', '<>', $semester->id)
                    ->where(function($q) use ($semester) {
                        if (is_null($semester->batch_id)) {
                            $q->whereNull('batch_id');
                        } else {
                            $q->where('batch_id', $semester->batch_id);
                        }
                    })
                    ->update(['is_active' => false, 'is_current' => false, 'is_locked' => true]);
            }

            // Sync semester courses automatically based on the new semester_number
            $intended = (int) $request->input('semester_number');
            // remove existing offerings
            $semester->semesterCourses()->delete();
            $coursesToAttach = Course::where('intended_semester', $intended)->pluck('id')->toArray();
            foreach ($coursesToAttach as $courseId) {
                SemesterCourse::create([
                    'semester_id' => $semester->id,
                    'course_id' => $courseId,
                    'max_students' => 60,
                    'enrolled_students' => 0,
                    'is_available' => true,
                ]);
            }

            DB::commit();
            return redirect()->route('authority.semesters')->with('success', 'Semester updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update semester', ['exception' => $e->getMessage()]);
            $msg = app()->environment('local') ? $e->getMessage() : 'Failed to update semester.';
            return back()->with('error', $msg)->withInput();
        }
    }

    public function activateSemester(Semester $semester)
    {
        DB::beginTransaction();
        try {
            // Prevent activation if this semester has been locked (previously deactivated)
            if ($semester->is_locked) {
                return back()->with('error', 'This semester cannot be reactivated.');
            }

            // Set this semester as active. Deactivate and lock other semesters for the same batch only.
            $semester->update(['is_active' => true]);

            // mark as current for the batch
            $semester->update(['is_current' => true]);

            Semester::where('id', '<>', $semester->id)
                ->where(function($q) use ($semester) {
                    if (is_null($semester->batch_id)) {
                        $q->whereNull('batch_id');
                    } else {
                        $q->where('batch_id', $semester->batch_id);
                    }
                })
                ->update(['is_active' => false, 'is_current' => false, 'is_locked' => true]);

            DB::commit();
            return back()->with('success', 'Semester activated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to activate semester.');
        }
    }

    /**
     * Return the next semester number for a given batch_year (query param).
     * GET /authority/semesters/next-number?batch_year=2024
     */
    public function nextSemesterNumber(Request $request)
    {
        $request->validate([
            'batch_year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $batchYear = $request->query('batch_year');
        $batchId = null;
        if ($batchYear) {
            $batch = Batch::where('year', $batchYear)->first();
            $batchId = $batch?->id;
        }

        // If no batch year provided, return 0 as the semantic for "no batch"
        if (empty($batchYear)) {
            return response()->json(['next' => 0]);
        }

        $last = Semester::where('batch_id', $batchId)->orderBy('semester_number', 'desc')->first();
        $next = $last ? ($last->semester_number + 1) : 1;

        return response()->json(['next' => $next]);
    }

    public function destroySemester(Semester $semester)
    {
        // Prevent deleting a semester if there are course registrations tied to it
        $registrationCount = $semester->courseRegistrations()->count();
        if ($registrationCount > 0) {
            return back()->with('error', 'Cannot delete this semester: there are ' . $registrationCount . ' course registrations associated with it.');
        }

        DB::beginTransaction();
        try {
            $semester->delete();
            DB::commit();
            return back()->with('success', 'Semester deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete semester.');
        }
    }

    public function semesterRegistrations(Semester $semester)
    {
        // Group registrations by student (one application per student per semester)
        $studentApplications = CourseRegistration::where('semester_id', $semester->id)
            ->with(['semesterCourse.course', 'student.profile'])
            ->get()
            ->groupBy('student_id')
            ->map(function ($grouped, $studentId) {
                $student = $grouped->first()->student;
                $courses = $grouped->map(function ($r) { return $r->semesterCourse->course; })->unique('id')->values();

                // Determine application status
                $hasHeadApproved = $grouped->contains(function ($r) { return in_array($r->status, ['head_approved', 'completed']); });
                $hasAdvisorApproved = $grouped->contains(function ($r) { return $r->status === 'advisor_approved'; });
                $hasRejected = $grouped->contains(function ($r) { return $r->status === 'rejected'; });

                if ($hasHeadApproved) {
                    $status = 'approved';
                } elseif ($hasRejected && ! $hasHeadApproved) {
                    $status = 'rejected';
                } elseif ($hasAdvisorApproved) {
                    $status = 'advisor_approved';
                } else {
                    $status = 'pending';
                }

                return (object) [
                    'student' => $student,
                    'courses' => $courses,
                    'status' => $status,
                    'registrations' => $grouped,
                ];
            })->values();

        // Simple pagination of the collection (convert to LengthAwarePaginator)
        $page = request()->get('page', 1);
        $perPage = 25;
        $total = $studentApplications->count();
        $items = $studentApplications->forPage($page, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return view('authority.semesters.registrations', ['semester' => $semester, 'registrations' => $paginator]);
    }
    
    // Course Management
    public function courses()
    {
        $query = Course::query();

        // Search by code or name
        if (request()->filled('q')) {
            $q = request()->get('q');
            $query->where(function($qry) use ($q) {
                $qry->where('course_code', 'like', "%{$q}%")
                    ->orWhere('course_name', 'like', "%{$q}%");
            });
        }

        // Filter by intended_semester (1..12)
        if (request()->filled('semester')) {
            $query->where('intended_semester', request()->get('semester'));
        }

        $courses = $query->withCount('semesterCourses')->orderBy('course_code')->paginate(20)->withQueryString();
        return view('authority.courses.index', compact('courses'));
    }

    /**
     * Return JSON list of courses for a given intended semester number
     */
    public function coursesByIntendedSemester($number)
    {
        $num = (int) $number;
        if ($num < 1 || $num > 12) {
            return response()->json(['error' => 'Invalid semester number'], 400);
        }

        $courses = Course::where('intended_semester', $num)->orderBy('course_code')->get(['id','course_code','course_name','credit_hours','course_type']);
        return response()->json($courses);
    }
    
    public function createCourse()
    {
        return view('authority.courses.create');
    }
    
    public function storeCourse(Request $request)
    {
        $request->validate([
            'course_code' => 'required|string|max:20|unique:courses',
            'course_name' => 'required|string|max:200',
            'credits' => 'required|numeric|min:0|max:10',
            'description' => 'nullable|string',
            'intended_semester' => 'required|integer|min:1|max:12',
            'course_type' => 'required|in:theory,lab,theory_lab',
        ]);

        $sem = (int) $request->input('intended_semester');
        $type = $request->input('course_type');

        // Enforce limits: max 5 theory, max 4 lab per semester.
        $theoryCount = Course::where('intended_semester', $sem)
            ->where(function($q){ $q->where('course_type','theory')->orWhere('course_type','theory_lab'); })->count();
        $labCount = Course::where('intended_semester', $sem)
            ->where(function($q){ $q->where('course_type','lab')->orWhere('course_type','theory_lab'); })->count();

        // If adding a theory or theory_lab, ensure theory count will not exceed 5
        if (in_array($type, ['theory','theory_lab']) && $theoryCount >= 5) {
            return back()->withInput()->with('error', 'Cannot add more theory courses for this semester (limit 5).');
        }
        // If adding a lab or theory_lab, ensure lab count will not exceed 4
        if (in_array($type, ['lab','theory_lab']) && $labCount >= 4) {
            return back()->withInput()->with('error', 'Cannot add more lab courses for this semester (limit 4).');
        }

        Course::create([
            'course_code' => $request->input('course_code'),
            'course_name' => $request->input('course_name'),
            'credit_hours' => $request->input('credits'),
            'description' => $request->input('description'),
            'intended_semester' => $sem,
            'course_type' => $type,
            'is_active' => true,
        ]);

        return redirect()->route('authority.courses')->with('success', 'Course created successfully.');
    }
    
    public function editCourse(Course $course)
    {
        return view('authority.courses.edit', compact('course'));
    }
    
    public function updateCourse(Request $request, Course $course)
    {
        $request->validate([
            'course_code' => 'required|string|max:20|unique:courses,course_code,' . $course->id,
            'course_name' => 'required|string|max:200',
            'credits' => 'required|numeric|min:0|max:10',
            'description' => 'nullable|string',
            'intended_semester' => 'required|integer|min:1|max:12',
            'course_type' => 'required|in:theory,lab,theory_lab',
        ]);

        $newSem = (int) $request->input('intended_semester');
        $newType = $request->input('course_type');

        // Enforce limits for target semester excluding this course itself
        $theoryCount = Course::where('intended_semester', $newSem)
            ->where(function($q){ $q->where('course_type','theory')->orWhere('course_type','theory_lab'); })
            ->where('id','!=',$course->id)
            ->count();
        $labCount = Course::where('intended_semester', $newSem)
            ->where(function($q){ $q->where('course_type','lab')->orWhere('course_type','theory_lab'); })
            ->where('id','!=',$course->id)
            ->count();

        if (in_array($newType, ['theory','theory_lab']) && $theoryCount >= 5) {
            return back()->withInput()->with('error', 'Cannot move/update: target semester already has maximum theory courses (5).');
        }
        if (in_array($newType, ['lab','theory_lab']) && $labCount >= 4) {
            return back()->withInput()->with('error', 'Cannot move/update: target semester already has maximum lab courses (4).');
        }

        $course->update([
            'course_code' => $request->input('course_code'),
            'course_name' => $request->input('course_name'),
            'credit_hours' => $request->input('credits'),
            'description' => $request->input('description'),
            'intended_semester' => $newSem,
            'course_type' => $newType,
        ]);

        return redirect()->route('authority.courses')->with('success', 'Course updated successfully.');
    }

    /**
     * Delete a course (only if there are no semester offerings tied to it)
     */
    public function destroyCourse(Course $course)
    {
        // Prevent deletion if the course is attached to any semester offerings
        if ($course->semesterCourses()->count() > 0) {
            return back()->with('error', 'Cannot delete course: it is attached to one or more semester offerings.');
        }

        try {
            $course->delete();
            return redirect()->route('authority.courses')->with('success', 'Course deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete course.');
        }
    }
    
    // User Management
    public function users()
    {
        $users = User::with('profile')->paginate(20);
        return view('authority.users.index', compact('users'));
    }
    
    public function createUser()
    {
        $batches = Batch::all();
        $advisors = User::where('role', 'advisor')->get();
        return view('authority.users.create', compact('batches', 'advisors'));
    }
    
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,advisor,department_head,authority',
            'student_id' => 'required_if:role,student|nullable|string|unique:user_profiles',
            'phone' => 'nullable|string|max:20',
            'batch_id' => 'required_if:role,student|nullable|exists:batches,id',
            'advisor_id' => 'required_if:role,student|nullable|exists:users,id',
        ]);
        
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
            
            // Create profile for students
            if ($request->role === 'student') {
                $user->profile()->create([
                    'student_id' => $request->student_id,
                    'phone' => $request->phone,
                    'batch_id' => $request->batch_id,
                    'advisor_id' => $request->advisor_id,
                ]);
            }
            
            DB::commit();
            return redirect()->route('authority.users')->with('success', 'User created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create user.')->withInput();
        }
    }

    public function editUser(User $user)
    {
        $batches = Batch::all();
        $advisors = User::where('role', 'advisor')->get();
        return view('authority.users.edit', compact('user', 'batches', 'advisors'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:student,advisor,department_head,authority',
            'student_id' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'batch_id' => 'nullable|exists:batches,id',
            'advisor_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $user->update($request->only(['name', 'email', 'role']));

            if ($request->role === 'student') {
                $profileData = [
                    'student_id' => $request->student_id,
                    'phone' => $request->phone,
                    'batch_id' => $request->batch_id,
                    'advisor_id' => $request->advisor_id,
                ];
                if ($user->profile) {
                    $user->profile->update($profileData);
                } else {
                    $user->profile()->create($profileData);
                }
            } else {
                // Remove student profile if role changed away from student
                if ($user->profile) {
                    $user->profile()->delete();
                }
            }

            DB::commit();
            return redirect()->route('authority.users')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update user.')->withInput();
        }
    }
    
    // Fee Management
    public function fees()
    {
    // eager-load semester courses and their course to compute credit totals in the view
    $fees = Fee::with(['batch', 'semester.semesterCourses.course'])->paginate(15);
        return view('authority.fees.index', compact('fees'));
    }
    
    public function createFee()
    {
        $batches = Batch::all();
        $semesters = Semester::all();
        return view('authority.fees.create', compact('batches', 'semesters'));
    }

    public function editFee(Fee $fee)
    {
        $batches = Batch::all();
        $semesters = Semester::all();
        return view('authority.fees.edit', compact('fee', 'batches', 'semesters'));
    }

    public function updateFee(Request $request, Fee $fee)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'per_credit_fee' => 'required|numeric|min:0',
            'admission_fee' => 'nullable|numeric|min:0',
            'lab_fee' => 'nullable|numeric|min:0',
            'library_fee' => 'nullable|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'fee_description' => 'nullable|string',
        ]);

        $fee->update([
            'semester_id' => $request->semester_id,
            'per_credit_fee' => $request->per_credit_fee,
            'admission_fee' => $request->admission_fee ?? 0,
            'lab_fee' => $request->lab_fee ?? 0,
            'library_fee' => $request->library_fee ?? 0,
            'other_fees' => $request->other_fees ?? 0,
            'fee_description' => $request->fee_description,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : $fee->is_active,
        ]);

        return redirect()->route('authority.fees')->with('success', 'Fee updated successfully.');
    }
    
    public function storeFee(Request $request)
    {
        $request->validate([
            'batch_id' => 'nullable|exists:batches,id',
            'semester_id' => 'required|exists:semesters,id',
            'tuition_fee' => 'nullable|numeric|min:0',
            'per_credit_fee' => 'nullable|numeric|min:0',
            'lab_fee' => 'nullable|numeric|min:0',
            'library_fee' => 'nullable|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'admission_fee' => 'nullable|numeric|min:0',
            'fee_description' => 'nullable|string',
        ]);

        // prefer explicit per_credit_fee, fall back to tuition_fee input for backward compatibility
        $perCredit = $request->input('per_credit_fee') ?? $request->input('tuition_fee');

        if (is_null($perCredit)) {
            return back()->with('error', 'Per-credit fee (or tuition_fee) is required.')->withInput();
        }

        Fee::create([
            'semester_id' => $request->semester_id,
            'per_credit_fee' => $perCredit,
            'admission_fee' => $request->input('admission_fee', 0),
            'library_fee' => $request->input('library_fee', 0),
            'lab_fee' => $request->input('lab_fee', 0),
            'other_fees' => $request->input('other_fees', 0),
            'fee_description' => $request->input('fee_description'),
            'is_active' => true,
        ]);

        return redirect()->route('authority.fees')->with('success', 'Fee structure created successfully.');
    }
    
    // Payment Verification
    public function paymentSlips()
    {
        $paymentSlips = PaymentSlip::with(['student.profile', 'semester'])
            ->where('payment_status', 'paid')
            ->latest()
            ->paginate(20);
        
        return view('authority.payment-slips', compact('paymentSlips'));
    }
    
    public function payments()
    {
        return $this->paymentSlips();
    }
    
    public function verifyPayment(Request $request, PaymentSlip $paymentSlip)
    {
        $request->validate([
            'payment_remarks' => 'nullable|string|max:500',
        ]);
        
        if ($paymentSlip->payment_status !== 'paid') {
            return back()->with('error', 'This payment slip has not been submitted for verification.');
        }
        
        $paymentSlip->update([
            'payment_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'payment_remarks' => $request->payment_remarks,
        ]);
        
        return back()->with('success', 'Payment verified successfully.');
    }
}

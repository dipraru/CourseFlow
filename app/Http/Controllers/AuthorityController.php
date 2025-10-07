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
        
        // Recent payment slips for verification
        $recentPayments = PaymentSlip::with(['student.profile', 'semester'])
            ->where('payment_status', 'paid')
            ->latest()
            ->take(10)
            ->get();
        
        return view('authority.dashboard', compact('stats', 'recentPayments', 'currentSemester'));
    }
    
    // Semester Management
    public function semesters()
    {
        // Include counts for semester courses and distinct student registrations (one student = one registration)
        $semesters = Semester::select('semesters.*', DB::raw('(select count(distinct student_id) from course_registrations where course_registrations.semester_id = semesters.id) as student_registrations_count'))
            ->withCount(['semesterCourses'])
            ->latest()
            ->paginate(15);
        return view('authority.semesters.index', compact('semesters'));
    }
    
    public function createSemester()
    {
        $courses = Course::orderBy('course_code')->get();
        return view('authority.semesters.create', compact('courses'));
    }
    
    public function storeSemester(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:semesters',
            'type' => 'required|in:Spring,Summer,Fall',
            'year' => 'required|integer|min:2020|max:2050',
            'semester_number' => 'required|integer|min:1',
            'registration_start_date' => 'required|date',
            'registration_end_date' => 'required|date|after:registration_start_date',
            'semester_start_date' => 'required|date',
            'semester_end_date' => 'required|date|after:semester_start_date',
            'is_current' => 'boolean',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id',
        ]);
        
        DB::beginTransaction();
        try {
            // If this semester is marked as current, unset others
            if ($request->boolean('is_current')) {
                Semester::where('is_current', true)->update(['is_current' => false]);
            }

            // Map request inputs to model columns
            $data = $request->only([
                'name', 'type', 'year', 'semester_number',
                'registration_start_date', 'registration_end_date',
                'semester_start_date', 'semester_end_date', 'is_current'
            ]);

            // If marked current, also mark as active for registration availability
            if (!empty($data['is_current'])) {
                $data['is_active'] = true;
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

            $createdSemester = Semester::create($data);

            // If courses were selected, attach them as SemesterCourse entries
            if ($request->filled('courses')) {
                foreach ($request->input('courses') as $courseId) {
                    SemesterCourse::create([
                        'semester_id' => $createdSemester->id,
                        'course_id' => $courseId,
                        'max_students' => 60,
                        'enrolled_students' => 0,
                        'is_available' => true,
                    ]);
                }
            }
            
            DB::commit();
            return redirect()->route('authority.semesters')->with('success', 'Semester created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create semester.')->withInput();
        }
    }
    
    public function editSemester(Semester $semester)
    {
        $courses = Course::orderBy('course_code')->get();
        $selected = $semester->semesterCourses()->pluck('course_id')->toArray();
        return view('authority.semesters.edit', compact('semester', 'courses', 'selected'));
    }
    
    public function updateSemester(Request $request, Semester $semester)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:semesters,name,' . $semester->id,
            'type' => 'required|in:Spring,Summer,Fall',
            'year' => 'required|integer|min:2020|max:2050',
            'semester_number' => 'required|integer|min:1',
            'registration_start_date' => 'required|date',
            'registration_end_date' => 'required|date|after:registration_start_date',
            'semester_start_date' => 'required|date',
            'semester_end_date' => 'required|date|after:semester_start_date',
            'is_current' => 'boolean',
        ]);
        
        DB::beginTransaction();
        try {
            // If this semester is marked as current, unset others
            if ($request->boolean('is_current') && !$semester->is_current) {
                Semester::where('is_current', true)->update(['is_current' => false]);
            }

            $data = $request->only([
                'name', 'type', 'year', 'semester_number',
                'registration_start_date', 'registration_end_date',
                'semester_start_date', 'semester_end_date', 'is_current'
            ]);

            // If semester is being marked current, also mark active
            if (!empty($data['is_current'])) {
                $data['is_active'] = true;
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

            // If courses were sent during update, sync semester courses (simple approach: remove existing and add new)
            if ($request->has('courses')) {
                // delete existing semester courses
                $semester->semesterCourses()->delete();
                foreach ($request->input('courses', []) as $courseId) {
                    SemesterCourse::create([
                        'semester_id' => $semester->id,
                        'course_id' => $courseId,
                        'max_students' => 60,
                        'enrolled_students' => 0,
                        'is_available' => true,
                    ]);
                }
            }
            
            DB::commit();
            return redirect()->route('authority.semesters')->with('success', 'Semester updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update semester.')->withInput();
        }
    }

    public function activateSemester(Semester $semester)
    {
        DB::beginTransaction();
        try {
            // Set this semester as current and active (allow multiple current semesters)
            $semester->update(['is_current' => true, 'is_active' => true]);
            DB::commit();
            return back()->with('success', 'Semester marked as current successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark semester as current.');
        }
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
        $courses = Course::withCount('semesterCourses')->paginate(15);
        return view('authority.courses.index', compact('courses'));
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
        ]);
        
        Course::create($request->all());
        
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
        ]);
        
        $course->update($request->all());
        
        return redirect()->route('authority.courses')->with('success', 'Course updated successfully.');
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
            'verified_by' => auth()->id(),
            'payment_remarks' => $request->payment_remarks,
        ]);
        
        return back()->with('success', 'Payment verified successfully.');
    }
}

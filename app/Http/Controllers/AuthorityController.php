<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Semester;
use App\Models\Course;
use App\Models\SemesterCourse;
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
        $semesters = Semester::withCount('semesterCourses')->latest()->paginate(15);
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
            'year' => 'required|integer|min:2020|max:2050',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start|before:end_date',
            'is_current' => 'boolean',
        ]);
        
        DB::beginTransaction();
        try {
            // If this semester is marked as current, unset others
            if ($request->is_current) {
                Semester::where('is_current', true)->update(['is_current' => false]);
            }
            
            Semester::create($request->all());
            
            DB::commit();
            return redirect()->route('authority.semesters')->with('success', 'Semester created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create semester.')->withInput();
        }
    }
    
    public function editSemester(Semester $semester)
    {
        return view('authority.semesters.edit', compact('semester'));
    }
    
    public function updateSemester(Request $request, Semester $semester)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:semesters,name,' . $semester->id,
            'year' => 'required|integer|min:2020|max:2050',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start|before:end_date',
            'is_current' => 'boolean',
        ]);
        
        DB::beginTransaction();
        try {
            // If this semester is marked as current, unset others
            if ($request->is_current && !$semester->is_current) {
                Semester::where('is_current', true)->update(['is_current' => false]);
            }
            
            $semester->update($request->all());
            
            DB::commit();
            return redirect()->route('authority.semesters')->with('success', 'Semester updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update semester.')->withInput();
        }
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
    
    // Fee Management
    public function fees()
    {
        $fees = Fee::with(['batch', 'semester'])->paginate(15);
        return view('authority.fees.index', compact('fees'));
    }
    
    public function createFee()
    {
        $batches = Batch::all();
        $semesters = Semester::all();
        return view('authority.fees.create', compact('batches', 'semesters'));
    }
    
    public function storeFee(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'semester_id' => 'required|exists:semesters,id',
            'tuition_fee' => 'required|numeric|min:0',
            'lab_fee' => 'nullable|numeric|min:0',
            'library_fee' => 'nullable|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
        ]);
        
        $totalAmount = $request->tuition_fee + 
                      ($request->lab_fee ?? 0) + 
                      ($request->library_fee ?? 0) + 
                      ($request->other_fees ?? 0);
        
        Fee::create([
            'batch_id' => $request->batch_id,
            'semester_id' => $request->semester_id,
            'tuition_fee' => $request->tuition_fee,
            'lab_fee' => $request->lab_fee,
            'library_fee' => $request->library_fee,
            'other_fees' => $request->other_fees,
            'total_amount' => $totalAmount,
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

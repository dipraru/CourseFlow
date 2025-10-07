<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdvisorController;
use App\Http\Controllers\DepartmentHeadController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\CourseRegistrationController;
use App\Http\Controllers\PaymentSlipController;
use Illuminate\Support\Facades\Route;

// Welcome page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard - redirects based on role
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    return match($user->role) {
        'student' => redirect()->route('student.dashboard'),
        'advisor' => redirect()->route('advisor.dashboard'),
        'department_head' => redirect()->route('head.dashboard'),
        'authority' => redirect()->route('authority.dashboard'),
        default => abort(403),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Student Routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/courses', [StudentController::class, 'courses'])->name('courses');
    Route::get('/registrations', [StudentController::class, 'registrations'])->name('registrations');
    Route::post('/register-courses', [CourseRegistrationController::class, 'store'])->name('register.store');
    Route::get('/payment-slip/{paymentSlip}', [PaymentSlipController::class, 'show'])->name('payment-slip.show');
    Route::get('/payment-slip/{paymentSlip}/download', [PaymentSlipController::class, 'download'])->name('payment-slip.download');
});

// Advisor Routes
Route::middleware(['auth', 'role:advisor'])->prefix('advisor')->name('advisor.')->group(function () {
    Route::get('/dashboard', [AdvisorController::class, 'dashboard'])->name('dashboard');
    Route::get('/students', [AdvisorController::class, 'students'])->name('students');
    Route::get('/pending-registrations', [AdvisorController::class, 'pendingRegistrations'])->name('pending');
    Route::post('/approve/{registration}', [AdvisorController::class, 'approve'])->name('approve');
    Route::post('/reject/{registration}', [AdvisorController::class, 'reject'])->name('reject');
});

// Department Head Routes
Route::middleware(['auth', 'role:department_head'])->prefix('head')->name('head.')->group(function () {
    Route::get('/dashboard', [DepartmentHeadController::class, 'dashboard'])->name('dashboard');
    Route::get('/pending-approvals', [DepartmentHeadController::class, 'pendingApprovals'])->name('pending');
    Route::post('/approve/{registration}', [DepartmentHeadController::class, 'approve'])->name('approve');
    Route::post('/reject/{registration}', [DepartmentHeadController::class, 'reject'])->name('reject');
    Route::get('/statistics', [DepartmentHeadController::class, 'statistics'])->name('statistics');
});

// Authority Routes
Route::middleware(['auth', 'role:authority'])->prefix('authority')->name('authority.')->group(function () {
    Route::get('/dashboard', [AuthorityController::class, 'dashboard'])->name('dashboard');
    
    // Semester Management
    Route::get('/semesters', [AuthorityController::class, 'semesters'])->name('semesters');
    Route::post('/semesters', [AuthorityController::class, 'storeSemester'])->name('semesters.store');
    Route::patch('/semesters/{semester}/activate', [AuthorityController::class, 'activateSemester'])->name('semesters.activate');
    
    // Course Management
    Route::get('/courses', [AuthorityController::class, 'courses'])->name('courses');
    Route::post('/courses', [AuthorityController::class, 'storeCourse'])->name('courses.store');
    Route::patch('/courses/{course}', [AuthorityController::class, 'updateCourse'])->name('courses.update');
    
    // User Management
    Route::get('/users', [AuthorityController::class, 'users'])->name('users');
    Route::post('/users', [AuthorityController::class, 'storeUser'])->name('users.store');
    
    // Fee Management
    Route::get('/fees', [AuthorityController::class, 'fees'])->name('fees');
    Route::post('/fees', [AuthorityController::class, 'storeFee'])->name('fees.store');
    
    // Payment Verification
    Route::get('/payments', [AuthorityController::class, 'payments'])->name('payments');
    Route::post('/payments/{paymentSlip}/verify', [AuthorityController::class, 'verifyPayment'])->name('payments.verify');
});

require __DIR__.'/auth.php';

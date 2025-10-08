@extends('layouts.dashboard')

@section('title', 'Student Dashboard')
@section('page-title', 'Dashboard')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('student.dashboard') }}" class="nav-link active">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('student.courses') }}" class="nav-link">
            <i class="bi bi-book"></i>
            <span>Available Courses</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('student.registrations') }}" class="nav-link">
            <i class="bi bi-journal-check"></i>
            <span>My Registrations</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('profile.edit') }}" class="nav-link">
            <i class="bi bi-person-circle"></i>
            <span>My Profile</span>
        </a>
    </li>
</ul>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-start">
            <h2 class="mb-3">Welcome, {{ Auth::user()->name }}!</h2>
            <div>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">Edit Profile</a>
            </div>
        </div>
        @if($currentSemester)
            <div class="alert alert-info alert-modern">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Current Semester:</strong> {{ $currentSemester->name }} {{ $currentSemester->year }}
                <br>
                <small>Registration Period:
                    @if($currentSemester->registration_start && $currentSemester->registration_end)
                        {{ $currentSemester->registration_start->format('M d, Y') }} - {{ $currentSemester->registration_end->format('M d, Y') }}
                    @else
                        Dates not set
                    @endif
                </small>
            </div>
        @else
            <div class="alert alert-warning alert-modern">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                No active semester. Please wait for the next registration period.
            </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-book"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Registrations</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                    <div class="stat-label">Pending Approval</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['approved'] }}</div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['rejected'] }}</div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Registrations -->
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i>Recent Registrations</span>
                <a href="{{ route('student.registrations') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($registrations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Credits</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registrations as $registration)
                                    <tr>
                                        <td>
                                            <strong>{{ $registration->semesterCourse->course->course_code }}</strong><br>
                                            <small class="text-muted">{{ $registration->semesterCourse->course->course_name }}</small>
                                        </td>
                                        <td>{{ $registration->semesterCourse->course->credit_hours }}</td>
                                        <td>{{ $registration->semester->name }}</td>
                                        <td>
                                            @if($registration->status === 'pending')
                                                <span class="badge badge-status bg-warning">Pending</span>
                                            @elseif($registration->status === 'advisor_approved')
                                                <span class="badge badge-status bg-info">Advisor Approved</span>
                                            @elseif($registration->status === 'approved')
                                                <span class="badge badge-status bg-success">Approved</span>
                                            @elseif($registration->status === 'rejected')
                                                <span class="badge badge-status bg-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>{{ $registration->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">No registrations yet</p>
                        <a href="{{ route('student.courses') }}" class="btn btn-primary">Register for Courses</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Payment Slips -->
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern">
                <i class="bi bi-receipt me-2"></i>Payment Slips
            </div>
            <div class="card-body">
                @if($paymentSlips->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($paymentSlips as $slip)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>{{ $slip->semester->name }}</strong>
                                    @if($slip->payment_status === 'unpaid')
                                        <span class="badge bg-danger">Unpaid</span>
                                    @elseif($slip->payment_status === 'paid')
                                        <span class="badge bg-warning">Pending Verification</span>
                                    @elseif($slip->payment_status === 'verified')
                                        <span class="badge bg-success">Verified</span>
                                    @endif
                                </div>
                                <div class="text-muted mb-2" style="font-size: 0.9rem;">
                                    Amount: <strong>৳{{ number_format($slip->total_amount, 2) }}</strong>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('student.payment-slip.show', $slip) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('student.payment-slip.download', $slip) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    @if($slip->payment_status === 'unpaid')
                                        <form action="{{ route('student.payment-slip.submit', $slip) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-success" onclick="return confirm('Have you paid the fees at the bank?')">
                                                <i class="bi bi-check"></i> Submit
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-receipt" style="font-size: 2.5rem;"></i>
                        <p class="mt-2 mb-0">No payment slips yet</p>
                        <small>Complete your course registration first</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Authority Dashboard')
@section('page-title', 'Authority Dashboard')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('authority.dashboard') }}" class="nav-link active">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('authority.semesters') }}" class="nav-link">
            <i class="bi bi-calendar-event"></i>
            <span>Semesters</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('authority.courses') }}" class="nav-link">
            <i class="bi bi-book"></i>
            <span>Courses</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('authority.users') }}" class="nav-link">
            <i class="bi bi-people"></i>
            <span>Users</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('authority.fees') }}" class="nav-link">
            <i class="bi bi-cash-stack"></i>
            <span>Fees</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('authority.payments') }}" class="nav-link">
            <i class="bi bi-receipt"></i>
            <span>Payments</span>
        </a>
    </li>
</ul>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-3">Welcome, {{ Auth::user()->name }}!</h2>
        @if($currentSemester)
            <div class="alert alert-info alert-modern">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Current Semester:</strong> {{ $currentSemester->name }} {{ $currentSemester->year }}
            </div>
        @else
            <div class="alert alert-warning alert-modern">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                No active semester.
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
                    <i class="bi bi-people"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['total_students'] ?? 0 }}</div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-book"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['total_courses'] ?? 0 }}</div>
                    <div class="stat-label">Total Courses</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['active_semester'] ?? 'None' }}</div>
                    <div class="stat-label">Active Semester</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['pending_payments'] ?? 0 }}</div>
                    <div class="stat-label">Pending Payments</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i>Recent Payment Submissions</span>
                <a href="{{ route('authority.payments') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentPayments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Semester</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $slip)
                                    <tr>
                                        <td>
                                            <strong>{{ optional($slip->student)->name ?? 'Unknown' }}</strong><br>
                                            <small class="text-muted">{{ optional($slip->student)->email ?? '' }}</small>
                                        </td>
                                        <td>{{ optional($slip->semester)->name ?? '' }}</td>
                                        <td>৳{{ number_format($slip->total_amount, 2) }}</td>
                                        <td>
                                            @if($slip->payment_status === 'unpaid')
                                                <span class="badge badge-status bg-danger">Unpaid</span>
                                            @elseif($slip->payment_status === 'paid')
                                                <span class="badge badge-status bg-warning">Pending Verification</span>
                                            @elseif($slip->payment_status === 'verified')
                                                <span class="badge badge-status bg-success">Verified</span>
                                            @endif
                                        </td>
                                        <td>{{ $slip->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">No recent payment slips</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern">
                <i class="bi bi-receipt me-2"></i>Payment Summary
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <div class="d-flex justify-content-between">
                            <div>Pending Verification</div>
                            <div><strong>{{ $stats['pending_payments'] ?? 0 }}</strong></div>
                        </div>
                    </li>
                    <li class="mb-3">
                        <div class="d-flex justify-content-between">
                            <div>Verified</div>
                            <div><strong>{{ $stats['verified_payments'] ?? 0 }}</strong></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Department Head Dashboard')
@section('page-title', 'Department Head Dashboard')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('head.dashboard') }}" class="nav-link active">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('head.pending') }}" class="nav-link">
            <i class="bi bi-journal-check"></i>
            <span>Pending Approvals</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('head.statistics') }}" class="nav-link">
            <i class="bi bi-bar-chart"></i>
            <span>Statistics</span>
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
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['pending_approvals'] ?? 0 }}</div>
                    <div class="stat-label">Pending Approvals</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['approved_today'] ?? 0 }}</div>
                    <div class="stat-label">Approved Today</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-journal-bookmark"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['total_registrations'] ?? 0 }}</div>
                    <div class="stat-label">Total Registrations</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['payment_slips_generated'] ?? 0 }}</div>
                    <div class="stat-label">Payment Slips</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i>Recent Approvals</span>
                <a href="{{ route('head.pending') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentApprovals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Dept Approved At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentApprovals as $reg)
                                    <tr>
                                        <td>
                                            <strong>{{ optional($reg->student)->name ?? $reg->student_id }}</strong><br>
                                            <small class="text-muted">{{ optional($reg->student)->email ?? '' }}</small>
                                        </td>
                                        <td>{{ optional($reg->semesterCourse->course)->course_name ?? '-' }}</td>
                                        <td>{{ ucfirst($reg->status) }}</td>
                                        <td>{{ $reg->dept_head_approved_at?->format('M d, Y H:i') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">No recent approvals</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern">
                <i class="bi bi-list-check me-2"></i>Approval Summary
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <div class="d-flex justify-content-between">
                            <div>Pending Approvals</div>
                            <div><strong>{{ $stats['pending_approvals'] ?? 0 }}</strong></div>
                        </div>
                    </li>
                    <li class="mb-3">
                        <div class="d-flex justify-content-between">
                            <div>Approved Today</div>
                            <div><strong>{{ $stats['approved_today'] ?? 0 }}</strong></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

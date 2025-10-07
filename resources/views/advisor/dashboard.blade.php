@extends('layouts.dashboard')

@section('title', 'Advisor Dashboard')
@section('page-title', 'Advisor Dashboard')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('advisor.dashboard') }}" class="nav-link active">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('advisor.students') }}" class="nav-link">
            <i class="bi bi-people"></i>
            <span>Students</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('advisor.pending') }}" class="nav-link">
            <i class="bi bi-journal-check"></i>
            <span>Pending Registrations</span>
        </a>
    </li>
</ul>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-3">Welcome, {{ $advisor->name }}!</h2>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['students'] ?? 0 }}</div>
                    <div class="stat-label">Students</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="stat-label">Pending Registrations</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
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
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i>Recent Registrations</span>
                <a href="{{ route('advisor.pending') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentRegistrations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Semester</th>
                                    <th>Applied At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRegistrations as $reg)
                                    <tr>
                                        <td>
                                            <strong>{{ optional($reg->student)->name ?? $reg->student_id }}</strong><br>
                                            <small class="text-muted">{{ optional($reg->student)->email ?? '' }}</small>
                                        </td>
                                        <td>{{ optional($reg->semesterCourse->course)->course_name ?? '-' }}</td>
                                        <td>{{ optional($reg->semester)->name ?? '-' }}</td>
                                        <td>{{ $reg->created_at?->format('M d, Y H:i') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">No recent registrations</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern">Quick Actions</div>
            <div class="card-body">
                <a href="{{ route('advisor.pending') }}" class="btn btn-primary w-100 mb-2">View Pending Registrations</a>
                <a href="{{ route('advisor.students') }}" class="btn btn-outline-secondary w-100">View Students</a>
            </div>
        </div>
    </div>
</div>
@endsection

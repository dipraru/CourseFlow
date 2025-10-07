@extends('layouts.dashboard')

@section('title', 'My Registrations')
@section('page-title', 'My Registrations')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('student.dashboard') }}" class="nav-link">
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
        <a href="{{ route('student.registrations') }}" class="nav-link active">
            <i class="bi bi-journal-check"></i>
            <span>My Registrations</span>
        </a>
    </li>
</ul>
@endsection

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <i class="bi bi-journal-check me-2"></i> My Registrations
    </div>
    <div class="card-body">
        @if($registrations->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Applied At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $registration)
                            <tr>
                                <td>
                                    <strong>{{ $registration->semesterCourse->course->course_code ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $registration->semesterCourse->course->course_name ?? '' }}</small>
                                </td>
                                <td>{{ $registration->semester->name ?? '' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $registration->status)) }}</span>
                                </td>
                                <td>{{ $registration->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $registrations->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">No registrations found.</p>
                <a href="{{ route('student.courses') }}" class="btn btn-primary">Register for Courses</a>
            </div>
        @endif
    </div>
</div>
@endsection

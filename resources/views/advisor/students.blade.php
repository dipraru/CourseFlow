@extends('layouts.dashboard')

@section('title', 'Advisor - Students')
@section('page-title', 'Students')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('advisor.dashboard') }}" class="nav-link">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('advisor.students') }}" class="nav-link active">
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
        <h2 class="mb-3">Your Students</h2>
    </div>
</div>

<div class="card-modern">
    <div class="card-body">
        @if($students->count())
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Batch</th>
                            <th>Email</th>
                            <th>Registrations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td><strong>{{ $student->name }}</strong></td>
                                <td>{{ optional($student->profile->batch)->name ?? '-' }}</td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->courseRegistrations->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $students->links() }}</div>
        @else
            <div class="p-4 text-center text-muted">
                <i class="bi bi-people" style="font-size: 3rem;"></i>
                <p class="mt-2">No students assigned.</p>
            </div>
        @endif
    </div>
</div>
@endsection

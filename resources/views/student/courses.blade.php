@extends('layouts.dashboard')

@section('title', 'Available Courses')
@section('page-title', 'Available Courses')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('student.dashboard') }}" class="nav-link">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('student.courses') }}" class="nav-link active">
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
</ul>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-3">Course Registration</h2>
        @if($currentSemester)
            <div class="alert alert-info alert-modern">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>{{ $currentSemester->name }} {{ $currentSemester->year }}</strong> | 
                Registration Deadline: 
                @if($currentSemester->registration_end)
                    {{ $currentSemester->registration_end->format('F d, Y') }}
                @else
                    Not set
                @endif
            </div>
        @endif
    </div>
</div>

@if($currentSemester && $availableCourses->count() > 0)
    <div class="card-modern">
        <div class="card-header-modern">
            <i class="bi bi-list-check me-2"></i>Select Courses to Register
        </div>
        <div class="card-body">
            <form action="{{ route('student.register.store') }}" method="POST">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $currentSemester->id }}">
                
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th width="50">Select</th>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Credits</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($availableCourses as $semesterCourse)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input course-checkbox" 
                                                   type="checkbox" 
                                                name="semester_courses[]" 
                                                   value="{{ $semesterCourse->id }}"
                                                   id="course_{{ $semesterCourse->id }}"
                                                data-credits="{{ $semesterCourse->course->credit_hours }}">
                                        </div>
                                    </td>
                                    <td><strong>{{ $semesterCourse->course->course_code }}</strong></td>
                                    <td>{{ $semesterCourse->course->course_name }}</td>
                                    <td>{{ $semesterCourse->course->credit_hours }}</td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $semesterCourse->course->description ?? 'No description available' }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 p-3 bg-light rounded">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <strong>Total Credits Selected: </strong>
                            <span id="totalCredits" class="fs-4 text-primary">0</span>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send-check"></i> Submit Registration
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="card-modern">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #94a3b8;"></i>
            <h4 class="mt-3">No Courses Available</h4>
            <p class="text-muted">
                @if(!$currentSemester)
                    No active semester. Please wait for the next registration period.
                @else
                    You have already registered for all available courses or no courses are offered this semester.
                @endif
            </p>
            <a href="{{ route('student.dashboard') }}" class="btn btn-primary mt-2">
                <i class="bi bi-house"></i> Back to Dashboard
            </a>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
    // Calculate total credits
    const checkboxes = document.querySelectorAll('.course-checkbox');
    const totalCreditsDisplay = document.getElementById('totalCredits');
    
    function updateTotalCredits() {
        let total = 0;
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                total += parseFloat(checkbox.dataset.credits);
            }
        });
        totalCreditsDisplay.textContent = total.toFixed(1);
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalCredits);
    });
</script>
@endpush

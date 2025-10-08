@extends('layouts.dashboard')
@section('title','Courses')
@section('page-title','Courses')
@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item"><a href="{{ route('authority.dashboard') }}" class="nav-link">Dashboard</a></li>
    <li class="nav-item"><a href="{{ route('authority.courses') }}" class="nav-link active">Courses</a></li>
</ul>
@endsection
@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <h3 class="mb-0">Courses</h3>
    <a href="{{ route('authority.courses.create') }}" class="btn btn-primary">Create Course</a>
</div>

<div class="card-modern p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-4">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by code or name">
        </div>
        <div class="col-md-3">
            <select name="semester" class="form-select">
                <option value="">All Semesters</option>
                @for($i=1;$i<=12;$i++)
                    <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary">Filter</button>
        </div>
    </form>
</div>

<div class="card-modern">
    <div class="card-body p-0">
        @if($courses->count())
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Credits</th>
                            <th>Type</th>
                            <th>Intended Sem</th>
                            <th>Offerings</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                            <tr>
                                <td>{{ $course->course_code }}</td>
                                <td>{{ $course->course_name }}</td>
                                <td>{{ $course->credit_hours }}</td>
                                <td>{{ ucfirst($course->course_type) }}</td>
                                <td>{{ $course->intended_semester }}</td>
                                <td>{{ $course->semester_courses_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('authority.courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('authority.courses.destroy', $course) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this course? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger ms-2">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                <nav aria-label="Courses pagination">
                    {{-- Use Bootstrap 5 pagination template and keep query string params --}}
                    {{ $courses->withQueryString()->links('pagination::bootstrap-5') }}
                </nav>
            </div>
        @else
            <div class="p-4 text-center text-muted">No courses found.</div>
        @endif
    </div>
</div>

@endsection

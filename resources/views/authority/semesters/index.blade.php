@extends('layouts.dashboard')

@section('title', 'Semesters')
@section('page-title', 'Semesters')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('authority.dashboard') }}" class="nav-link">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('authority.semesters') }}" class="nav-link active">
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
</ul>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Semesters</h3>
        <!-- Creation endpoint uses POST /authority/semesters; no separate create page is defined in routes -->
        <a href="#" class="btn btn-primary disabled">Create Semester</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card-modern">
            <div class="card-body p-0">
                @if($semesters->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Year</th>
                                    <th>Courses</th>
                                    <th>Active</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($semesters as $semester)
                                    <tr>
                                        <td>{{ $semester->name }}</td>
                                        <td>{{ ucfirst($semester->type ?? 'n/a') }}</td>
                                        <td>{{ $semester->year }}</td>
                                        <td>{{ $semester->semester_courses_count ?? 0 }}</td>
                                        <td>
                                            @if($semester->is_current)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(!$semester->is_current)
                                                <form action="{{ route('authority.semesters.activate', $semester) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Activate this semester?')">Activate</button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled>Activated</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">
                        {{ $semesters->withQueryString()->links() }}
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                        <p class="mt-2 mb-0">No semesters found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

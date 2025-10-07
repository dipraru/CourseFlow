@extends('layouts.dashboard')

@section('title', 'Pending Approvals')
@section('page-title', 'Pending Approvals')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('head.dashboard') }}" class="nav-link">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('head.pending') }}" class="nav-link active">
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
        <h2 class="mb-3">Pending Approvals</h2>
    </div>
</div>

<div class="card-modern">
    <div class="card-body">
        @if($registrations->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Batch</th>
                            <th>Course</th>
                            <th>Applied At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $reg)
                            <tr>
                                <td>
                                    <strong>{{ optional($reg->student)->name ?? $reg->student_id }}</strong><br>
                                    <small class="text-muted">{{ optional($reg->student)->email ?? '' }}</small>
                                </td>
                                <td>{{ optional(optional($reg->student)->profile)->batch->name ?? '-' }}</td>
                                <td>{{ optional($reg->semesterCourse->course)->course_name ?? '-' }}</td>
                                <td>{{ $reg->created_at?->format('M d, Y H:i') ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('head.approve', $reg) }}" method="POST" onsubmit="return confirm('Approve this registration?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>

                                        <form action="{{ route('head.reject', $reg) }}" method="POST" onsubmit="return confirm('Reject this registration?');">
                                            @csrf
                                            <input type="hidden" name="rejection_reason" value="Rejected by department head">
                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $registrations->links() }}
            </div>
        @else
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-2">No pending approvals.</p>
            </div>
        @endif
    </div>
</div>
@endsection

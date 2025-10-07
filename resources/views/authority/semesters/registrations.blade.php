@extends('layouts.dashboard')

@section('title', 'Semester Registrations')
@section('page-title', 'Semester Registrations')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <h3 class="mb-0">Registrations for {{ $semester->name }} ({{ $semester->year }})</h3>
    <a href="{{ route('authority.semesters') }}" class="btn btn-secondary">Back to Semesters</a>
</div>

<div class="card-modern">
    <div class="card-body p-0">
        @if($registrations->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $reg)
                            <tr>
                                <td>
                                    <strong>{{ optional($reg->student)->name }}</strong><br>
                                    <small class="text-muted">{{ optional($reg->student)->email }}</small>
                                </td>
                                <td>{{ optional($reg->semesterCourse->course)->course_code }} - {{ optional($reg->semesterCourse->course)->course_name }}</td>
                                <td>{{ ucfirst($reg->status) }}</td>
                                <td>{{ $reg->applied_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $registrations->links() }}
            </div>
        @else
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                <p class="mt-2 mb-0">No registrations for this semester yet.</p>
            </div>
        @endif
    </div>
</div>

@endsection

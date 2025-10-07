@extends('layouts.dashboard')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <i class="bi bi-person-lines-fill me-2"></i> Edit User
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('authority.users.update', $user) }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                    <option value="advisor" {{ $user->role === 'advisor' ? 'selected' : '' }}>Advisor</option>
                    <option value="department_head" {{ $user->role === 'department_head' ? 'selected' : '' }}>Department Head</option>
                    <option value="authority" {{ $user->role === 'authority' ? 'selected' : '' }}>Authority</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Student ID (if student)</label>
                <input type="text" name="student_id" value="{{ old('student_id', optional($user->profile)->student_id) }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', optional($user->profile)->phone) }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Batch</label>
                <select name="batch_id" class="form-select">
                    <option value="">-- Select batch --</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ optional($user->profile)->batch_id == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Advisor</label>
                <select name="advisor_id" class="form-select">
                    <option value="">-- Select advisor --</option>
                    @foreach($advisors as $adv)
                        <option value="{{ $adv->id }}" {{ optional($user->profile)->advisor_id == $adv->id ? 'selected' : '' }}>{{ $adv->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <i class="bi bi-person-plus me-2"></i> Create User
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('authority.users.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="student">Student</option>
                    <option value="advisor">Advisor</option>
                    <option value="department_head">Department Head</option>
                    <option value="authority">Authority</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Student ID (if creating student)</label>
                <input type="text" name="student_id" value="{{ old('student_id') }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Batch</label>
                <select name="batch_id" class="form-select">
                    <option value="">-- Select batch --</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Advisor (if creating student)</label>
                <select name="advisor_id" class="form-select">
                    <option value="">-- Select advisor --</option>
                    @foreach($advisors as $adv)
                        <option value="{{ $adv->id }}">{{ $adv->name }} ({{ $adv->email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection

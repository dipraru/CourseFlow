@extends('layouts.dashboard')

@section('title', 'Edit Fee')
@section('page-title', 'Edit Fee Structure')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <i class="bi bi-pencil-square me-2"></i> Edit Fee Structure
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('authority.fees.update', $fee) }}">
            @csrf
            @method('PATCH')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Batch</label>
                    <select name="batch_id" class="form-select">
                        <option value="">-- Select Batch --</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}" {{ $fee->batch_id == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select" required>
                        <option value="">-- Select Semester --</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ $fee->semester_id == $semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Per-credit Fee</label>
                    <input type="number" step="0.01" name="per_credit_fee" value="{{ old('per_credit_fee', $fee->per_credit_fee) }}" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Admission Fee</label>
                    <input type="number" step="0.01" name="admission_fee" value="{{ old('admission_fee', $fee->admission_fee) }}" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Lab Fee</label>
                    <input type="number" step="0.01" name="lab_fee" value="{{ old('lab_fee', $fee->lab_fee) }}" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Library Fee</label>
                    <input type="number" step="0.01" name="library_fee" value="{{ old('library_fee', $fee->library_fee) }}" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Other Fees</label>
                    <input type="number" step="0.01" name="other_fees" value="{{ old('other_fees', $fee->other_fees) }}" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <input type="text" name="fee_description" value="{{ old('fee_description', $fee->fee_description) }}" class="form-control">
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $fee->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Create Fee Structure')
@section('page-title', 'Create Fee Structure')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <i class="bi bi-cash-stack me-2"></i> Create Fee Structure
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('authority.fees.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Batch</label>
                    <select name="batch_id" class="form-select" required>
                        <option value="">-- Select Batch --</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select" required>
                        <option value="">-- Select Semester --</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Per-credit Fee</label>
                    <input type="number" step="0.01" name="per_credit_fee" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Lab Fee</label>
                    <input type="number" step="0.01" name="lab_fee" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Library Fee</label>
                    <input type="number" step="0.01" name="library_fee" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Other Fees</label>
                    <input type="number" step="0.01" name="other_fees" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Admission Fee</label>
                    <input type="number" step="0.01" name="admission_fee" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="fee_description" class="form-control">
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Create Fee Structure</button>
            </div>
        </form>
    </div>
</div>
@endsection

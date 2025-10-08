@extends('layouts.dashboard')
@section('title','Create Course')
@section('page-title','Create Course')
@section('content')
<div class="card-modern p-3">
	<form action="{{ route('authority.courses.store') }}" method="POST">
		@csrf
		<div class="mb-3">
			<label class="form-label">Course Code</label>
			<input type="text" name="course_code" value="{{ old('course_code') }}" class="form-control" required>
		</div>
		<div class="mb-3">
			<label class="form-label">Course Name</label>
			<input type="text" name="course_name" value="{{ old('course_name') }}" class="form-control" required>
		</div>
		<div class="mb-3 row">
			<div class="col-md-4">
				<label class="form-label">Credits</label>
				<input type="number" step="0.5" name="credits" value="{{ old('credits',3) }}" class="form-control" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">Intended Semester</label>
				<select name="intended_semester" class="form-select" required>
					@for($i=1;$i<=12;$i++)
						<option value="{{ $i }}" {{ old('intended_semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
					@endfor
				</select>
			</div>
			<div class="col-md-4">
				<label class="form-label">Course Type</label>
				<select name="course_type" class="form-select" required>
					<option value="theory">Theory</option>
					<option value="lab">Lab</option>
					<option value="theory_lab">Theory + Lab</option>
				</select>
			</div>
		</div>
		<div class="mb-3">
			<label class="form-label">Description</label>
			<textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
		</div>
		<div class="d-flex justify-content-end">
			<a href="{{ route('authority.courses') }}" class="btn btn-light me-2">Cancel</a>
			<button class="btn btn-primary">Create Course</button>
		</div>
	</form>
</div>
@endsection

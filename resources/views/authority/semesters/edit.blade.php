@extends('layouts.dashboard')
@section('title','Edit Semester')
@section('page-title','Edit Semester')

@section('sidebar')
<ul class="list-unstyled">
	<li class="nav-item"><a href="{{ route('authority.dashboard') }}" class="nav-link">Dashboard</a></li>
	<li class="nav-item"><a href="{{ route('authority.semesters') }}" class="nav-link">Semesters</a></li>
</ul>
@endsection

@section('content')
<div class="card-modern p-4">
	<form action="{{ route('authority.semesters.update', $semester) }}" method="POST">
		@csrf
		@method('PATCH')

		<div class="mb-3">
			<label class="form-label">Name</label>
			<input type="text" name="name" value="{{ old('name', $semester->name) }}" class="form-control">
			@error('name')<div class="text-danger small">{{ $message }}</div>@enderror
		</div>

		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label">Type</label>
				<select name="type" class="form-select">
					<option value="">Select</option>
					<option value="Spring" {{ old('type', $semester->type) == 'Spring' ? 'selected' : '' }}>Spring</option>
					<option value="Summer" {{ old('type', $semester->type) == 'Summer' ? 'selected' : '' }}>Summer</option>
					<option value="Fall" {{ old('type', $semester->type) == 'Fall' ? 'selected' : '' }}>Fall</option>
				</select>
				@error('type')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>

			<div class="col-md-4">
				<label class="form-label">Year</label>
				<input type="number" name="year" value="{{ old('year', $semester->year) }}" class="form-control">
				@error('year')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>

			<div class="col-md-4">
				<label class="form-label">Semester Number</label>
				<input type="number" name="semester_number" value="{{ old('semester_number', $semester->semester_number) }}" class="form-control">
				@error('semester_number')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
		</div>

			<div class="row g-3 mt-3">
				<div class="col-md-6">
					<label class="form-label">For Batch (optional)</label>
					<select name="batch_id" class="form-select">
						<option value="">All Batches</option>
						@foreach($batches as $batch)
							<option value="{{ $batch->id }}" {{ old('batch_id', $semester->batch_id) == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
						@endforeach
					</select>
					@error('batch_id')<div class="text-danger small">{{ $message }}</div>@enderror
				</div>
			</div>

		<hr class="my-4">

		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Registration Start</label>
				<input type="date" name="registration_start_date" value="{{ old('registration_start_date', optional($semester->registration_start_date)->format('Y-m-d')) }}" class="form-control">
				@error('registration_start_date')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
			<div class="col-md-6">
				<label class="form-label">Registration End</label>
				<input type="date" name="registration_end_date" value="{{ old('registration_end_date', optional($semester->registration_end_date)->format('Y-m-d')) }}" class="form-control">
				@error('registration_end_date')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
		</div>

		<div class="row g-3 mt-3">
			<div class="col-md-6">
				<label class="form-label">Semester Start</label>
				<input type="date" name="semester_start_date" value="{{ old('semester_start_date', optional($semester->semester_start_date)->format('Y-m-d')) }}" class="form-control">
				@error('semester_start_date')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
			<div class="col-md-6">
				<label class="form-label">Semester End</label>
				<input type="date" name="semester_end_date" value="{{ old('semester_end_date', optional($semester->semester_end_date)->format('Y-m-d')) }}" class="form-control">
				@error('semester_end_date')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
		</div>

		<div class="form-check form-switch mt-3">
			<input class="form-check-input" type="checkbox" role="switch" id="is_current" name="is_current" value="1" {{ $semester->is_current ? 'checked' : '' }}>
			<label class="form-check-label" for="is_current">Mark as current semester</label>
		</div>

		<hr class="my-3">

		<div class="mb-3">
			<label class="form-label">Courses that will be included (auto-populated by semester number)</label>
			<div id="auto-courses" class="border rounded p-2" style="min-height:120px; background:#f8f9fa;">
				<div class="text-muted">Select a semester number above to see the courses that will be attached automatically.</div>
			</div>
			<small class="text-muted">Courses are auto-selected from the course catalog based on Intended Semester. You don't need to choose them manually.</small>
		</div>

		<div class="mt-4">
			<button class="btn btn-primary">Update Semester</button>
			<a href="{{ route('authority.semesters') }}" class="btn btn-secondary">Cancel</a>
		</div>
	</form>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
	const semInput = document.querySelector('input[name="semester_number"]');
	const autoDiv = document.getElementById('auto-courses');
	function loadCourses(){
		const val = semInput.value || 0;
		if (!val || val < 1 || val > 12) {
			autoDiv.innerHTML = '<div class="text-muted">Select a semester number above to see the courses that will be attached automatically.</div>';
			return;
		}
		autoDiv.innerHTML = '<div class="text-muted">Loading courses...</div>';
		fetch('{{ route("authority.courses.by_semester", ["number"=>":num"]) }}'.replace(':num', val))
			.then(r => r.json())
			.then(data => {
				if (data && data.length) {
					let html = '<ul class="list-unstyled mb-0">';
					data.forEach(c => {
						html += `<li>${c.course_code} — ${c.course_name} <small class="text-muted">(${c.course_type})</small></li>`;
					});
					html += '</ul>';
					autoDiv.innerHTML = html;
				} else {
					autoDiv.innerHTML = '<div class="text-muted">No courses found for this semester.</div>';
				}
			}).catch(()=>{
				autoDiv.innerHTML = '<div class="text-danger">Failed to load courses.</div>';
			});
	}
	semInput.addEventListener('input', loadCourses);
	// initial load
	loadCourses();
});
</script>
@endpush

@endsection

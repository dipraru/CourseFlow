@extends('layouts.dashboard')
@section('title','Create Semester')
@section('page-title','Create Semester')

@section('sidebar')
<ul class="list-unstyled">
	<li class="nav-item"><a href="{{ route('authority.dashboard') }}" class="nav-link">Dashboard</a></li>
	<li class="nav-item"><a href="{{ route('authority.semesters') }}" class="nav-link">Semesters</a></li>
</ul>
@endsection

@section('content')
<div class="card-modern p-4">
	<form action="{{ route('authority.semesters.store') }}" method="POST">
		@csrf

		@if(session('error'))
		<div class="alert alert-danger">{{ session('error') }}</div>
		@endif
		@if(session('success'))
		<div class="alert alert-success">{{ session('success') }}</div>
		@endif
		@if($errors->any())
		<div class="alert alert-danger">
			<ul class="mb-0">
				@foreach($errors->all() as $err)
					<li>{{ $err }}</li>
				@endforeach
			</ul>
		</div>
		@endif

		<div class="mb-3">
			<label class="form-label">Name</label>
			<input type="text" name="name" value="{{ old('name') }}" class="form-control">
			@error('name')<div class="text-danger small">{{ $message }}</div>@enderror
		</div>

		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label">Type</label>
				<select name="type" class="form-select">
					<option value="">Select</option>
					<option value="Spring">Spring</option>
					<option value="Summer">Summer</option>
					<option value="Fall">Fall</option>
				</select>
				@error('type')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>

			<div class="col-md-4">
				<label class="form-label">Year</label>
				<input type="number" name="year" value="{{ old('year', date('Y')) }}" class="form-control">
				@error('year')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>

			<div class="col-md-4">
				<label class="form-label">Semester Number</label>
				<input type="number" name="semester_number" value="{{ old('semester_number', 1) }}" class="form-control">
				@error('semester_number')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
		</div>

		<div class="row g-3 mt-3">
			<div class="col-md-6">
				<label class="form-label">Batch Year (e.g. 2024)</label>
				<input type="number" name="batch_year" value="{{ old('batch_year') }}" class="form-control" placeholder="Enter batch year">
				<small class="text-muted">Specify the batch by its year. This creates the semester for that specific batch.</small>
				@error('batch_year')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
		</div>

		<hr class="my-4">

		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Registration Start</label>
				<input type="date" name="registration_start_date" value="{{ old('registration_start_date') }}" class="form-control">
				@error('registration_start_date')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
			<div class="col-md-6">
				<label class="form-label">Registration End</label>
				<input type="date" name="registration_end_date" value="{{ old('registration_end_date') }}" class="form-control">
				@error('registration_end_date')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
		</div>

		<div class="row g-3 mt-3">
			<div class="col-md-6">
				<label class="form-label">Semester Start</label>
				<input type="date" name="semester_start_date" value="{{ old('semester_start_date') }}" class="form-control">
				@error('semester_start_date')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
			<div class="col-md-6">
				<label class="form-label">Semester End</label>
				<input type="date" name="semester_end_date" value="{{ old('semester_end_date') }}" class="form-control">
				@error('semester_end_date')<div class="text-danger small">{{ $message }}</div>@enderror
			</div>
		</div>

		{{-- is_current removed: semesters are auto-activated for the batch when created --}}

		<hr class="my-3">

		<div class="mb-3">
			<label class="form-label">Courses that will be included (auto-populated by semester number)</label>
			<div id="auto-courses" class="border rounded p-2" style="min-height:120px; background:#f8f9fa;">
				<div class="text-muted">Select a semester number above to see the courses that will be attached automatically.</div>
			</div>
			<div id="auto-courses-meta" class="mt-2 small text-muted"></div>
			<small class="text-muted">Courses are auto-selected from the course catalog based on Intended Semester. You don't need to choose them manually.</small>
		</div>

		<div class="mt-4">
			<button class="btn btn-primary">Create Semester</button>
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
			document.getElementById('auto-courses-meta').innerHTML = '';
			return;
		}
		autoDiv.innerHTML = '<div class="text-muted">Loading courses...</div>';
		fetch('{{ route("authority.courses.by_semester", ["number"=>":num"]) }}'.replace(':num', val))
			.then(r => r.json())
			.then(data => {
				if (data && data.length) {
					// compute counts
					const theory = data.filter(c => c.course_type === 'theory' || c.course_type === 'theory_lab').length;
					const lab = data.filter(c => c.course_type === 'lab' || c.course_type === 'theory_lab').length;
					document.getElementById('auto-courses-meta').innerHTML = `<strong>Theory:</strong> ${theory} &nbsp; — &nbsp; <strong>Lab:</strong> ${lab}`;

					let html = '<ul class="list-unstyled mb-0">';
					data.forEach(c => {
						html += `<li>${c.course_code} — ${c.course_name} <small class="text-muted">(${c.course_type})</small></li>`;
					});
					html += '</ul>';
					autoDiv.innerHTML = html;
				} else {
					autoDiv.innerHTML = '<div class="text-muted">No courses found for this semester.</div>';
					document.getElementById('auto-courses-meta').innerHTML = '';
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

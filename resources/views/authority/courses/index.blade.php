@extends('layouts.dashboard')
@section('title','Courses')
@section('page-title','Courses')
@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item"><a href="{{ route('authority.dashboard') }}" class="nav-link">Dashboard</a></li>
    <li class="nav-item"><a href="{{ route('authority.courses') }}" class="nav-link active">Courses</a></li>
</ul>
@endsection
@section('content')
<div class="p-4">Courses management placeholder.</div>
@endsection

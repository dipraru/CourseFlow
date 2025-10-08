@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="max-w-3xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <div class="card-modern mx-auto">
        <div class="card-body p-8 text-center">
            @if(View::exists('components.application-logo'))
                <x-application-logo class="h-8 w-auto mx-auto mb-3" />
            @else
                <div class="mb-3 text-lg font-semibold">{{ config('app.name', 'CourseFlow') }}</div>
            @endif

            <div class="flex justify-center gap-3 mb-6">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary px-6 py-2 rounded-lg shadow-md transition-transform hover:-translate-y-0.5">Open Dashboard</a>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary px-6 py-2 rounded-lg">Profile</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary px-6 py-2 rounded-lg shadow-md transition-transform hover:-translate-y-0.5">Log in</a>
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary px-6 py-2 rounded-lg">Register</a>
                    @endif
                @endauth
            </div>

        </div>
    </div>
</div>
@endsection

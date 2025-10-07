@extends('layouts.dashboard')

@section('title', 'Users')
@section('page-title', 'Manage Users')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <i class="bi bi-people me-2"></i> Users
        <a href="{{ route('authority.users.create') }}" class="btn btn-primary btn-sm float-end">Create User</a>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-secondary">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">No users found.</p>
            </div>
        @endif
    </div>
</div>
@endsection

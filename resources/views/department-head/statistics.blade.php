@extends('layouts.dashboard')

@section('title', 'Department Head - Statistics')
@section('page-title', 'Statistics')

@section('sidebar')
<ul class="list-unstyled">
    <li class="nav-item">
        <a href="{{ route('head.dashboard') }}" class="nav-link">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('head.pending') }}" class="nav-link">
            <i class="bi bi-journal-check"></i>
            <span>Pending Approvals</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('head.statistics') }}" class="nav-link active">
            <i class="bi bi-bar-chart"></i>
            <span>Statistics</span>
        </a>
    </li>
</ul>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-3">Statistics</h2>
        @if($currentSemester)
            <div class="alert alert-info alert-modern">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Current Semester:</strong> {{ $currentSemester->name }} {{ $currentSemester->year }}
            </div>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern">Registration Status</div>
            <div class="card-body">
                @if(isset($statusStats) && $statusStats->count())
                    <ul class="list-unstyled">
                        @foreach($statusStats as $row)
                            <li class="mb-2 d-flex justify-content-between">
                                <div>{{ ucfirst($row->status) }}</div>
                                <div><strong>{{ $row->count }}</strong></div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No registration data available.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern">Payment Summary</div>
            <div class="card-body">
                @if(isset($paymentStats) && $paymentStats->count())
                    <ul class="list-unstyled">
                        @foreach($paymentStats as $row)
                            <li class="mb-2 d-flex justify-content-between">
                                <div>{{ ucfirst($row->payment_status) }}</div>
                                <div><strong>{{ $row->count }}</strong></div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No payment data available.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card-modern">
            <div class="card-header-modern">Batch-wise Registrations</div>
            <div class="card-body">
                @if(isset($batchStats) && $batchStats->count())
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Batch</th>
                                    <th>Students</th>
                                    <th>Registrations (current semester)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batchStats as $batchName => $users)
                                    <tr>
                                        <td>{{ $batchName }}</td>
                                        <td>{{ $users->count() }}</td>
                                        <td>{{ $users->sum(function($u){ return $u->courseRegistrations->count(); }) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No batch statistics available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

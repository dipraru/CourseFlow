@extends('layouts.dashboard')

@section('title', 'Fees')
@section('page-title', 'Fee Structures')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <i class="bi bi-cash-stack me-2"></i> Fee Structures
        <a href="{{ route('authority.createFee') }}" class="btn btn-primary btn-sm float-end">Create Fee</a>
    </div>
    <div class="card-body">
        @if($fees->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Batch</th>
                            <th>Semester</th>
                            <th>Total Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fees as $fee)
                            <tr>
                                <td>{{ $fee->batch->name ?? 'N/A' }}</td>
                                <td>{{ $fee->semester->name ?? 'N/A' }}</td>
                                <td>৳{{ number_format($fee->total_amount, 2) }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-secondary">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $fees->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">No fee structures found.</p>
            </div>
        @endif
    </div>
</div>
@endsection

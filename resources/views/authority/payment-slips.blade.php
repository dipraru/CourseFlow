@extends('layouts.dashboard')

@section('title', 'Payment Slips')
@section('page-title', 'Payment Slips to Verify')

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <i class="bi bi-receipt me-2"></i> Payment Slips
    </div>
    <div class="card-body">
        @if($paymentSlips->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Semester</th>
                            <th>Amount</th>
                            <th>Submitted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentSlips as $slip)
                            <tr>
                                <td>{{ $slip->student->name ?? 'N/A' }}</td>
                                <td>{{ $slip->semester->name ?? 'N/A' }}</td>
                                <td>৳{{ number_format($slip->amount, 2) }}</td>
                                <td>{{ $slip->submitted_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('authority.payments.verify', $slip) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Verify</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $paymentSlips->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">No payment slips pending verification.</p>
            </div>
        @endif
    </div>
</div>
@endsection

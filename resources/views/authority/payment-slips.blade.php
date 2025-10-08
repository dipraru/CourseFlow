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
                            <th>Verified By</th>
                            <th>Verified At</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentSlips as $slip)
                            <tr>
                                <td>{{ $slip->student->name ?? 'N/A' }}</td>
                                <td>{{ $slip->semester->name ?? 'N/A' }}</td>
                                <td>৳{{ number_format($slip->total_amount ?? $slip->amount ?? 0, 2) }}</td>
                                <td>{{ $slip->paid_at?->format('Y-m-d H:i') ?? $slip->generated_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                                <td>{{ $slip->verifiedBy?->name ?? '-' }}</td>
                                <td>{{ $slip->verified_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($slip->payment_remarks ?? '-', 60) }}</td>
                                <td>
                                    @if($slip->payment_status !== 'verified')
                                        <form method="POST" action="{{ route('authority.payments.verify', $slip) }}" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Verify</button>
                                        </form>
                                    @else
                                        <span class="text-success">Verified</span>
                                    @endif
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

@extends('layouts.app')

@section('title', 'Payment Slip')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Payment Slip: {{ $paymentSlip->slip_number }}</strong>
                    <div>
                        <a href="{{ route('student.payment-slip.download', $paymentSlip) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <p><strong>Student:</strong> {{ $paymentSlip->student->name }}</p>
                    <p><strong>Semester:</strong> {{ $paymentSlip->semester->name }} {{ $paymentSlip->semester->year ?? '' }}</p>
                    <p><strong>Generated:</strong> {{ $paymentSlip->generated_at ? $paymentSlip->generated_at->format('M d, Y H:i') : '-' }}</p>

                    <hr>

                    <h5>Registered Courses</h5>
                    <ul>
                        @foreach($paymentSlip->registered_courses ?? [] as $course)
                            <li>{{ $course['code'] ?? $course['name'] }} — {{ $course['name'] ?? '' }} ({{ $course['credit_hours'] ?? 0 }} cr)</li>
                        @endforeach
                    </ul>

                    <h5 class="mt-3">Fee Breakdown</h5>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Per Credit Fee</td>
                                <td class="text-end">৳{{ number_format($paymentSlip->fee_breakdown['per_credit_fee'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Credit Hours</td>
                                <td class="text-end">{{ $paymentSlip->fee_breakdown['credit_hours'] ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td>Admission Fee</td>
                                <td class="text-end">৳{{ number_format($paymentSlip->fee_breakdown['admission_fee'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Library Fee</td>
                                <td class="text-end">৳{{ number_format($paymentSlip->fee_breakdown['library_fee'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Lab Fee</td>
                                <td class="text-end">৳{{ number_format($paymentSlip->fee_breakdown['lab_fee'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Other Fees</td>
                                <td class="text-end">৳{{ number_format($paymentSlip->fee_breakdown['other_fees'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="table-active">
                                <th>Total</th>
                                <th class="text-end">৳{{ number_format($paymentSlip->total_amount ?? ($paymentSlip->fee_breakdown['calculated_total'] ?? 0), 2) }}</th>
                            </tr>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between mt-3">
                        <div>
                            <span class="badge bg-info">Status: {{ ucfirst($paymentSlip->status) }}</span>
                            <span class="badge bg-{{ $paymentSlip->payment_status === 'unpaid' ? 'danger' : ($paymentSlip->payment_status === 'paid' ? 'warning' : 'success') }}">{{ ucfirst($paymentSlip->payment_status) }}</span>
                        </div>

                        <div>
                            @if($paymentSlip->payment_status === 'unpaid')
                                <form action="{{ route('student.payment-slip.submit', $paymentSlip) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">I have paid at the bank</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if($paymentSlip->verified_at)
                        <hr>
                        <div>
                            <p><strong>Verified By:</strong> {{ $paymentSlip->verifiedBy?->name ?? $paymentSlip->verified_by }}</p>
                            <p><strong>Verified At:</strong> {{ $paymentSlip->verified_at?->format('M d, Y H:i') ?? '-' }}</p>
                            @if($paymentSlip->payment_remarks)
                                <p><strong>Remarks:</strong> {{ $paymentSlip->payment_remarks }}</p>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

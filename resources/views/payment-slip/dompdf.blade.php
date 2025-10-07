<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Slip - {{ $paymentSlip->slip_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .container { width: 100%; padding: 10px; }
        .header { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #333; padding: 6px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h3>Payment Slip</h3>
            <div>{{ $paymentSlip->slip_number }}</div>
        </div>

        <table>
            <tr>
                <td><strong>Student</strong></td>
                <td>{{ $paymentSlip->student->name }}</td>
                <td><strong>Semester</strong></td>
                <td>{{ $paymentSlip->semester->name }}</td>
            </tr>
        </table>

        <table>
            <thead>
                <tr><th>Code</th><th>Name</th><th class="text-right">Credits</th></tr>
            </thead>
            <tbody>
                @foreach($paymentSlip->registered_courses ?? [] as $course)
                    <tr>
                        <td>{{ $course['code'] ?? '' }}</td>
                        <td>{{ $course['name'] ?? '' }}</td>
                        <td class="text-right">{{ $course['credit_hours'] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table>
            <tbody>
                <tr><td>Per Credit Fee</td><td class="text-right">৳{{ number_format($paymentSlip->fee_breakdown['per_credit_fee'] ?? 0, 2) }}</td></tr>
                <tr><td>Admission Fee</td><td class="text-right">৳{{ number_format($paymentSlip->fee_breakdown['admission_fee'] ?? 0, 2) }}</td></tr>
                <tr class="text-right"><th>Total</th><th class="text-right">৳{{ number_format($paymentSlip->total_amount ?? ($paymentSlip->fee_breakdown['calculated_total'] ?? 0), 2) }}</th></tr>
            </tbody>
        </table>

        <div style="margin-top:20px;">
            <small>Generated: {{ optional($paymentSlip->generated_at)->format('d M, Y H:i') ?? '-' }}</small>
        </div>
    </div>
</body>
</html>

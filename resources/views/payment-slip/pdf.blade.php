<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Slip - {{ $paymentSlip->slip_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .page {
            width: 100%;
            padding: 10px;
        }
        
        .columns-container {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .column {
            display: table-cell;
            width: 33.33%;
            border: 2px solid #000;
            padding: 15px;
            vertical-align: top;
        }
        
        .column:nth-child(2) {
            border-left: 2px dashed #000;
            border-right: 2px dashed #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .university-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .department-name {
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .slip-title {
            font-size: 13px;
            font-weight: bold;
            background: #f0f0f0;
            padding: 5px;
            margin-bottom: 5px;
        }
        
        .copy-label {
            font-size: 10px;
            font-weight: bold;
            color: #666;
            margin-top: 3px;
        }
        
        .info-section {
            margin-bottom: 12px;
        }
        
        .info-row {
            margin-bottom: 6px;
            display: table;
            width: 100%;
        }
        
        .info-label {
            font-weight: bold;
            display: table-cell;
            width: 40%;
            padding-right: 5px;
        }
        
        .info-value {
            display: table-cell;
            width: 60%;
        }
        
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .fee-table th,
        .fee-table td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }
        
        .fee-table th {
            background: #e0e0e0;
            font-weight: bold;
        }
        
        .total-row {
            font-weight: bold;
            background: #f5f5f5;
        }
        
        .signature-section {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .signature-box {
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 10px;
        }
        
        .instructions {
            margin-top: 12px;
            padding: 8px;
            background: #fffacd;
            border: 1px solid #f0e68c;
            font-size: 9px;
        }
        
        .instructions ul {
            margin-left: 15px;
            margin-top: 5px;
        }
        
        .instructions li {
            margin-bottom: 3px;
        }
        
        .barcode {
            text-align: center;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="columns-container">
            <!-- Column 1: Student's Copy -->
            <div class="column">
                <div class="header">
                    <div class="university-name">UNIVERSITY NAME</div>
                    <div class="department-name">Department of Computer Science</div>
                    <div class="slip-title">PAYMENT SLIP</div>
                    <div class="copy-label">[STUDENT'S COPY]</div>
                </div>
                
                <div class="info-section">
                    <div class="info-row">
                        <div class="info-label">Slip No:</div>
                        <div class="info-value">{{ $paymentSlip->slip_number }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Student ID:</div>
                        <div class="info-value">{{ $paymentSlip->student->profile->student_id }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Name:</div>
                        <div class="info-value">{{ $paymentSlip->student->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Batch:</div>
                        <div class="info-value">{{ $paymentSlip->student->profile->batch->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Semester:</div>
                        <div class="info-value">{{ $paymentSlip->semester->name }} {{ $paymentSlip->semester->year }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Issue Date:</div>
                        <div class="info-value">{{ optional($paymentSlip->generated_at)->format('d M, Y') ?? '-' }}</div>
                    </div>
                </div>
                
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Fee Type</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tuition Fee</td>
                            <td style="text-align: right;">৳{{ number_format($paymentSlip->total_amount, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>TOTAL</strong></td>
                            <td style="text-align: right;"><strong>৳{{ number_format($paymentSlip->total_amount, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="barcode">
                    *{{ $paymentSlip->slip_number }}*
                </div>
                
                <div class="instructions">
                    <strong>Instructions:</strong>
                    <ul>
                        <li>Pay at any authorized bank branch</li>
                        <li>Keep this copy for your records</li>
                        <li>Payment deadline: {{ optional($paymentSlip->semester->registration_end)->format('d M, Y') ?? 'Not set' }}</li>
                    </ul>
                </div>
                
                <div class="signature-section">
                    <div class="signature-box">
                        Student's Signature
                    </div>
                </div>
            </div>
            
            <!-- Column 2: Bank's Copy -->
            <div class="column">
                <div class="header">
                    <div class="university-name">UNIVERSITY NAME</div>
                    <div class="department-name">Department of Computer Science</div>
                    <div class="slip-title">PAYMENT SLIP</div>
                    <div class="copy-label">[BANK'S COPY]</div>
                </div>
                
                <div class="info-section">
                    <div class="info-row">
                        <div class="info-label">Slip No:</div>
                        <div class="info-value">{{ $paymentSlip->slip_number }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Student ID:</div>
                        <div class="info-value">{{ $paymentSlip->student->profile->student_id }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Name:</div>
                        <div class="info-value">{{ $paymentSlip->student->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Batch:</div>
                        <div class="info-value">{{ $paymentSlip->student->profile->batch->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Semester:</div>
                        <div class="info-value">{{ $paymentSlip->semester->name }} {{ $paymentSlip->semester->year }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Issue Date:</div>
                        <div class="info-value">{{ optional($paymentSlip->generated_at)->format('d M, Y') ?? '-' }}</div>
                    </div>
                </div>
                
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Fee Type</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tuition Fee</td>
                            <td style="text-align: right;">৳{{ number_format($paymentSlip->total_amount, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>TOTAL</strong></td>
                            <td style="text-align: right;"><strong>৳{{ number_format($paymentSlip->total_amount, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="barcode">
                    *{{ $paymentSlip->slip_number }}*
                </div>
                
                <div class="signature-section">
                    <div style="margin-bottom: 10px;">
                        <div class="info-label">Payment Method:</div>
                        <div style="border-bottom: 1px solid #000; min-height: 20px; margin-top: 5px;"></div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <div class="info-label">Transaction ID:</div>
                        <div style="border-bottom: 1px solid #000; min-height: 20px; margin-top: 5px;"></div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <div class="info-label">Date:</div>
                        <div style="border-bottom: 1px solid #000; min-height: 20px; margin-top: 5px;"></div>
                    </div>
                    
                    <div class="signature-box" style="margin-top: 30px;">
                        Bank Officer's Signature & Stamp
                    </div>
                </div>
            </div>
            
            <!-- Column 3: Department Office's Copy -->
            <div class="column">
                <div class="header">
                    <div class="university-name">UNIVERSITY NAME</div>
                    <div class="department-name">Department of Computer Science</div>
                    <div class="slip-title">PAYMENT SLIP</div>
                    <div class="copy-label">[DEPARTMENT OFFICE'S COPY]</div>
                </div>
                
                <div class="info-section">
                    <div class="info-row">
                        <div class="info-label">Slip No:</div>
                        <div class="info-value">{{ $paymentSlip->slip_number }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Student ID:</div>
                        <div class="info-value">{{ $paymentSlip->student->profile->student_id }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Name:</div>
                        <div class="info-value">{{ $paymentSlip->student->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Batch:</div>
                        <div class="info-value">{{ $paymentSlip->student->profile->batch->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Semester:</div>
                        <div class="info-value">{{ $paymentSlip->semester->name }} {{ $paymentSlip->semester->year }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Issue Date:</div>
                        <div class="info-value">{{ optional($paymentSlip->generated_at)->format('d M, Y') ?? '-' }}</div>
                    </div>
                </div>
                
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Fee Type</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tuition Fee</td>
                            <td style="text-align: right;">৳{{ number_format($paymentSlip->total_amount, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>TOTAL</strong></td>
                            <td style="text-align: right;"><strong>৳{{ number_format($paymentSlip->total_amount, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="barcode">
                    *{{ $paymentSlip->slip_number }}*
                </div>
                
                <div class="instructions">
                    <strong>For Office Use Only:</strong>
                    <ul>
                        <li>Verify payment with bank copy</li>
                        <li>Update payment status in system</li>
                        <li>File for record keeping</li>
                    </ul>
                </div>
                
                <div class="signature-section">
                    <div style="margin-bottom: 10px;">
                        <div class="info-label">Received Date:</div>
                        <div style="border-bottom: 1px solid #000; min-height: 20px; margin-top: 5px;"></div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <div class="info-label">Verified By:</div>
                        <div style="border-bottom: 1px solid #000; min-height: 20px; margin-top: 5px;"></div>
                    </div>
                    
                    <div class="signature-box" style="margin-top: 30px;">
                        Authority Signature & Stamp
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

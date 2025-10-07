<?php

namespace App\Http\Controllers;

use App\Models\PaymentSlip;
use Illuminate\Http\Request;

class PaymentSlipController extends Controller
{
    public function show(PaymentSlip $paymentSlip)
    {
        // Ensure student can only view their own slip
        if (auth()->user()->role === 'student' && $paymentSlip->student_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $paymentSlip->load(['student.profile.batch', 'semester']);
        
        return view('payment-slip.show', compact('paymentSlip'));
    }
    
    public function download(PaymentSlip $paymentSlip)
    {
        // Ensure student can only download their own slip
        if (auth()->user()->role === 'student' && $paymentSlip->student_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $paymentSlip->load(['student.profile.batch', 'semester']);

        // If PDF generator package (barryvdh/laravel-dompdf) is installed, use it.
        if (app()->bound('dompdf.wrapper')) {
            // Use the bound dompdf wrapper to generate PDF
            $pdfWrapper = app('dompdf.wrapper');
            // Use a simplified DOMPDF-friendly view to avoid complex cellmap/frame errors
            $pdfWrapper->loadView('payment-slip.dompdf', compact('paymentSlip'));
            // Ensure standard A4 paper
            if (method_exists($pdfWrapper, 'setPaper')) {
                $pdfWrapper->setPaper('A4', 'portrait');
            }

            return $pdfWrapper->download('payment-slip-' . $paymentSlip->slip_number . '.pdf');
        }

        // Fallback: render the payment-slip PDF view as HTML so the user can print/save as PDF from browser.
        // This avoids a hard failure when DOMPDF is not installed.
        return response()->view('payment-slip.pdf', compact('paymentSlip'));
    }
    
    public function submitPayment(Request $request, PaymentSlip $paymentSlip)
    {
        // Ensure student can only submit their own slip
        if ($paymentSlip->student_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        if ($paymentSlip->payment_status !== 'unpaid') {
            return back()->with('error', 'This payment slip has already been submitted.');
        }
        
        $paymentSlip->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
        
        return back()->with('success', 'Payment slip submitted successfully. Awaiting verification from authority.');
    }

    // Backward-compatible route handler used by web.php -> student.payment-slip.submit
    public function submit(Request $request, PaymentSlip $paymentSlip)
    {
        return $this->submitPayment($request, $paymentSlip);
    }
}

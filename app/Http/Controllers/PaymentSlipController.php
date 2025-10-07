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
        
        // Generate PDF using a view
        $pdf = \PDF::loadView('payment-slip.pdf', compact('paymentSlip'));
        
        return $pdf->download('payment-slip-' . $paymentSlip->slip_number . '.pdf');
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
}

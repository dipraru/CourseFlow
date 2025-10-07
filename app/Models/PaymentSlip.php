<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'semester_id',
        'slip_number',
        'total_amount',
        'credit_hours',
        'fee_breakdown',
        'registered_courses',
        'status',
        'payment_status',
        'generated_at',
        'downloaded_at',
        'paid_at',
        'verified_at',
        'verified_by',
        'payment_remarks',
        'due_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'credit_hours' => 'decimal:1',
        'fee_breakdown' => 'array',
        'registered_courses' => 'array',
        'generated_at' => 'datetime',
        'downloaded_at' => 'datetime',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'due_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

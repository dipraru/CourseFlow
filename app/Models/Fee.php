<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_id',
        'per_credit_fee',
        'admission_fee',
        'library_fee',
        'lab_fee',
        'other_fees',
        'fee_description',
        'is_active',
    ];

    protected $casts = [
        'per_credit_fee' => 'decimal:2',
        'admission_fee' => 'decimal:2',
        'library_fee' => 'decimal:2',
        'lab_fee' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}

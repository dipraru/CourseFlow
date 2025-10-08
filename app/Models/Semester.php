<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'batch_id',
        'type',
        'year',
        'semester_number',
        'registration_start_date',
        'registration_end_date',
        'semester_start_date',
        'semester_end_date',
        'is_active',
        'is_current',
    ];

    protected $casts = [
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
        'semester_start_date' => 'date',
        'semester_end_date' => 'date',
        'is_active' => 'boolean',
        'is_current' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function semesterCourses()
    {
        return $this->hasMany(SemesterCourse::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class);
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function paymentSlips()
    {
        return $this->hasMany(PaymentSlip::class);
    }
}

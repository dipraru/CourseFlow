<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'semester_id',
        'semester_course_id',
        'status',
        'student_remarks',
        'rejection_reason',
        'total_fee',
        'applied_at',
        'advisor_approved_at',
        'head_approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'total_fee' => 'decimal:2',
        'applied_at' => 'datetime',
        'advisor_approved_at' => 'datetime',
        'head_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    public function semesterCourse()
    {
        return $this->belongsTo(SemesterCourse::class);
    }

    public function approvals()
    {
        return $this->hasMany(RegistrationApproval::class);
    }
}

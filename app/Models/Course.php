<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code',
        'course_name',
        'description',
        'credit_hours',
        'intended_semester',
        'course_type',
        'is_active',
    ];

    protected $casts = [
        'credit_hours' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function semesterCourses()
    {
        return $this->hasMany(SemesterCourse::class);
    }
}

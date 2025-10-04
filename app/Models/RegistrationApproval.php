<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_registration_id',
        'approver_id',
        'approver_role',
        'status',
        'comments',
        'action_taken_at',
    ];

    protected $casts = [
        'action_taken_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function courseRegistration()
    {
        return $this->belongsTo(CourseRegistration::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}

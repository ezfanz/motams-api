<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'status_id',
        'date',
        'reason',
        'created_by',
    ];

    /**
     * Relationship with Status.
     */
    public function status()
    {
        return $this->belongsTo(AttendanceStatus::class);
    }

    /**
     * Relationship with User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Approvals.
     */
    public function approvals()
    {
        return $this->hasMany(AttendanceApproval::class);
    }

    /**
     * Polymorphic relationship with Notifications.
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Relationship with AttendanceDetails.
     */
    public function details()
    {
        return $this->hasOne(AttendanceDetail::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status_id');
    }

    public function reviewStatus()
    {
        return $this->belongsTo(ReviewStatus::class);
    }

     /**
     * Relationship with ReviewStatus for verification status.
     */
    public function verificationStatus()
    {
        return $this->belongsTo(ReviewStatus::class, 'verification_status_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

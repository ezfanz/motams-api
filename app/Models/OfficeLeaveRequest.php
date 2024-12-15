<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


class OfficeLeaveRequest extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'created_by',
        'leave_type_id',
        'date',
        'day',
        'start_time',
        'end_time',
        'reason',
        'status',
        'approval_status_id',
        'approval_notes'
    ];

    /**
     * Define the relationship with the User (creator of the leave request).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with the User (reviewer of the leave request).
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }


    /**
     * Define the relationship with the LeaveType.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvalStatus()
    {
        return $this->belongsTo(ReviewStatus::class, 'approval_status_id');
    }

    /**
     * Accessor to calculate the remaining hours for the day.
     */
    public function getRemainingHoursAttribute()
    {
        $today = Carbon::now()->format('Y-m-d');
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        // Total hours of leave for this request
        $leaveHours = $startTime->diffInMinutes($endTime) / 60;

        // Total allowed hours (4 hours per day)
        $totalAllowedHours = 4;

        // Calculate remaining hours
        $remainingHours = $totalAllowedHours - $leaveHours;

        return $remainingHours > 0 ? $remainingHours : 0;
    }

    /**
     * Polymorphic relationship with ReasonTransaction.
     */
    public function reasonTransactions()
    {
        return $this->morphMany(ReasonTransaction::class, 'reasonable');
    }

    /**
     * Accessor to calculate the total hours for the leave request.
     */
    public function getTotalHoursAttribute()
    {
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        // Calculate total leave hours
        $totalHours = $startTime->diffInMinutes($endTime) / 60;

        return $totalHours > 0 ? floor($totalHours) . ' Jam ' . round((fmod($totalHours, 1) * 60)) . ' Minit' : '0 Jam 0 Minit';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles,  SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'staff_id',
        'name',
        'username',            // New username field
        'email',
        'password',
        'position',            // User's job title or position
        'department',          // User's department
        'reviewing_officer_id', // ID of the reviewing officer
        'approving_officer_id', // ID of the approving officer
        'last_login_at',
    ];

    // Ensure the last_login_at attribute is cast as a datetime
    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Check if the user has review permissions.
     *
     * @return bool
     */
    public function canReview()
    {
        return $this->can('review');
    }

    /**
     * Check if the user has approve permissions.
     *
     * @return bool
     */
    public function canApprove()
    {
        return $this->can('approve');
    }

    /**
     * Relationship to fetch the reviewing officer for this user.
     */
    public function reviewingOfficer()
    {
        return $this->belongsTo(User::class, 'reviewing_officer_id');
    }

    /**
     * Relationship to fetch the approving officer for this user.
     */
    public function approvingOfficer()
    {
        return $this->belongsTo(User::class, 'approving_officer_id');
    }

    /**
     * Relationship with AttendanceRecords.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Relationship with Approvals as a Reviewer.
     */
    public function reviews()
    {
        return $this->hasMany(AttendanceApproval::class, 'reviewed_by');
    }

    /**
     * Relationship with Approvals as an Approver.
     */
    public function approvals()
    {
        return $this->hasMany(AttendanceApproval::class, 'approved_by');
    }

    /**
     * Relationship with Notifications.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}

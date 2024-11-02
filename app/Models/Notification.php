<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'message',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Polymorphic relationship with notifiable models (attendance records, tasks, etc.).
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Relationship with User (Recipient of the notification).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

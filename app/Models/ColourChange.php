<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ColourChange extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'color_id',
        'start_date',
        'end_date',
        'change_count',
        'status',
        'approval_request',
        'manager_approval',
        'notes',
        'is_deleted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
}

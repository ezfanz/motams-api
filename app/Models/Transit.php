<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transit extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staffid',
        'trdate',
        'trdatetime',
        'card_number',
        'terminal',
        'direction',
        'strdirection',
        'telegram_flag',
        'ramadhan_yt',
        'is_deleted',
    ];

    /**
     * Relationships
     */

    // If this relates to a User (staff), use this:
    public function staff()
    {
        return $this->belongsTo(User::class, 'staffid', 'id');
    }

    // If this relates to calendars, use this:
    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'trdate', 'fulldate');
    }

    /**
     * Scope for filtering records that are not soft-deleted.
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }
}

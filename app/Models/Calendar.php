<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'calendars';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fulldate',
        'dayname',
        'isweekday',
        'isholiday',
        'holidaydesc',
        'is_ramadhan',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Scope a query to only include holidays.
     */
    public function scopeHolidays($query)
    {
        return $query->where('isholiday', true);
    }

    /**
     * Scope a query to only include weekdays.
     */
    public function scopeWeekdays($query)
    {
        return $query->where('isweekday', true);
    }

    /**
     * Scope a query to check for Ramadhan days.
     */
    public function scopeRamadhan($query)
    {
        return $query->where('is_ramadhan', true);
    }
}

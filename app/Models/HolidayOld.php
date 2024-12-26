<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HolidayOld extends Model
{
    use HasFactory;

    protected $table = 'holidayold';

    protected $fillable = [
        'dateholiday', 'description',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Alasan extends Model
{
    use HasFactory;

    protected $table = 'alasan';

    protected $fillable = [
        'diskripsi', 'late', 'early', 'absent', 'mytime_id',
    ];
}

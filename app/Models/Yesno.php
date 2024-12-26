<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Yesno extends Model
{
    use HasFactory;

    protected $table = 'yesno';

    protected $fillable = [
        'diskripsi', 'is_deleted',
    ];
}

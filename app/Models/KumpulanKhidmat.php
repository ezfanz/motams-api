<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KumpulanKhidmat extends Model
{
    use HasFactory;

    protected $table = 'kumpkhidmat';

    protected $fillable = [
        'diskripsi', 'diskripsi_ringkas',
    ];
}

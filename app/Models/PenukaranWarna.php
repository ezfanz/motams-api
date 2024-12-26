<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenukaranWarna extends Model
{
    use HasFactory;

    protected $table = 'penukaranwarna';

    protected $fillable = [
        'diskripsi', 'created_at', 'updated_at',
    ];
}

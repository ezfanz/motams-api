<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KalendarRamadhan extends Model
{
    use HasFactory;

    protected $table = 'kalendar_ramadhan';

    protected $fillable = [
        'tarikh_dari', 'tarikh_hingga', 'is_deleted', 'id_pencipta', 'pengguna',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WbfRamadhan extends Model
{
    use HasFactory;

    protected $table = 'wbf_ramadhan';

    protected $fillable = [
        'idpeg', 'staffid', 'tarikh_dari', 'tarikh_hingga', 'pilih_yt', 'id_pencipta', 'pengguna', 'created_at', 'updated_at',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'id_pencipta');
    }

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'pengguna');
    }
}

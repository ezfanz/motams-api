<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenukaranWarna extends Model
{
    use HasFactory;

    protected $table = 'penukaranwarna';

    protected $fillable = [
        'idpeg',
        'staffid',
        'tarikhdari',
        'tarikhhingga',
        'bilkali',
        'warna',
        'status',
        'srt_tnjk_sbb_yt',
        'kelulusan_kj',
        'catatan',
        'pgw_jana',
        'tkh_jana',
        'id_penyemak',
        'id_pengesah',
        'flag',
        'is_deleted',
        'id_pencipta',
        'pengguna',
        'created_at',
        'updated_at',
    ];
}

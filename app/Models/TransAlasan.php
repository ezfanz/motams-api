<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransAlasan extends Model
{
    use HasFactory;

    protected $table = 'trans_alasan';

    protected $fillable = [
        'idpeg',
        'log_datetime',
        'alasan_id',
        'jenisalasan_id',
        'catatan_peg',
        'peg_alasan',
        'tkh_peg_alasan',
        'penyemak_id',
        'status_penyemak',
        'catatan_penyemak',
        'tkh_penyemak',
        'pengesah_id',
        'status_pengesah',
        'catatan_pengesah',
        'tkh_pengesah',
        'status',
        'is_deleted',
        'id_pencipta',
        'pengguna',
        'created_at',
        'updated_at',
    ];

    /**
     * Soft delete column mapping.
     */
    protected $casts = [
        'log_datetime' => 'datetime',
        'tkh_peg_alasan' => 'datetime',
        'tkh_penyemak' => 'datetime',
        'tkh_pengesah' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'idpeg');
    }

    /**
     * Relationship with the Alasan model.
     */
    public function alasan()
    {
        return $this->belongsTo(Alasan::class, 'alasan_id');
    }

    /**
     * Relationship with the JenisAlasan model.
     */
    public function jenisAlasan()
    {
        return $this->belongsTo(JenisAlasan::class, 'jenisalasan_id');
    }

    /**
     * Relationship with the Penyemak user.
     */
    public function penyemak()
    {
        return $this->belongsTo(User::class, 'penyemak_id');
    }

    /**
     * Relationship with the Pengesah user.
     */
    public function pengesah()
    {
        return $this->belongsTo(User::class, 'pengesah_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status', 'id');
    }
}

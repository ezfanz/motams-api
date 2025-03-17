<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    use HasFactory;

    protected $table = 'status';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'diskripsi',
        'is_deleted',
        'id_pencipta',
        'pengguna',
    ];


   // **Status Constants**
   const MENUNGGU_SEMAKAN = 1;
   const DITERIMA_PENYEMAK = 2;
   const TIDAK_DITERIMA_PENYEMAK = 3;
   const DITERIMA_PENGESAH = 4;
   const TIDAK_DITERIMA_PENGESAH = 5;
   const MEMERLUKAN_MAKLUMAT_LANJUT = 6;
   const PENUKARAN_KAD_ASAL_KE_HIJAU = 7;
   const PENUKARAN_KAD_HIJAU_KE_MERAH = 8;
   const PENUKARAN_KAD_KEKAL_HIJAU = 9;
   const PENUKARAN_KAD_KEKAL_MERAH = 10;
   const PENUKARAN_KAD_MERAH_KE_HIJAU = 11;
   const PENUKARAN_KAD_HIJAU_KE_ASAL = 12;
   const DILULUSKAN_KETUA_JABATAN = 13;
   const TIDAK_DILULUSKAN_KETUA_JABATAN = 14;
   const BARU = 15;
   const DILULUSKAN = 16;
   const TIDAK_DILULUSKAN = 17;

  /**
     * Get status name by ID.
     */
    public static function getStatusName(int $statusId): string
    {
        $statusNames = [
            self::MENUNGGU_SEMAKAN => 'Menunggu Semakan',
            self::DITERIMA_PENYEMAK => 'Alasan Diterima Penyemak',
            self::TIDAK_DITERIMA_PENYEMAK => 'Alasan Tidak Diterima Penyemak',
            self::DITERIMA_PENGESAH => 'Alasan Diterima Pengesah',
            self::TIDAK_DITERIMA_PENGESAH => 'Alasan Tidak Diterima Pengesah',
            self::MEMERLUKAN_MAKLUMAT_LANJUT => 'Memerlukan Maklumat Lanjut',
            self::PENUKARAN_KAD_ASAL_KE_HIJAU => 'Penukaran Warna Kad Asal Ke Hijau',
            self::PENUKARAN_KAD_HIJAU_KE_MERAH => 'Penukaran Warna Kad Hijau Ke Merah',
            self::PENUKARAN_KAD_KEKAL_HIJAU => 'Penukaran Warna Kad Kekal Hijau',
            self::PENUKARAN_KAD_KEKAL_MERAH => 'Penukaran Warna Kad Kekal Merah',
            self::PENUKARAN_KAD_MERAH_KE_HIJAU => 'Penukaran Warna Kad Merah Ke Hijau',
            self::PENUKARAN_KAD_HIJAU_KE_ASAL => 'Penukaran Warna Kad Hijau Ke Asal',
            self::DILULUSKAN_KETUA_JABATAN => 'Diluluskan Ketua Jabatan',
            self::TIDAK_DILULUSKAN_KETUA_JABATAN => 'Tidak Diluluskan Ketua Jabatan',
            self::BARU => 'Baru',
            self::DILULUSKAN => 'Diluluskan',
            self::TIDAK_DILULUSKAN => 'Tidak Diluluskan',
        ];

        return $statusNames[$statusId] ?? 'Status Tidak Diketahui';
    }


     /**
     * Get color by status ID.
     */
    public static function getStatusColor(int $statusId): string
    {
        return match ($statusId) {
            self::DITERIMA_PENGESAH, self::DILULUSKAN, self::DILULUSKAN_KETUA_JABATAN => '#28a745', // Green
            self::DITERIMA_PENYEMAK => '#17a2b8', // Blue
            self::MENUNGGU_SEMAKAN,
            self::TIDAK_DITERIMA_PENYEMAK,
            self::TIDAK_DITERIMA_PENGESAH,
            self::TIDAK_DILULUSKAN_KETUA_JABATAN,
            self::MEMERLUKAN_MAKLUMAT_LANJUT => '#ffc107', // Yellow
            default => '#dc3545', // Red
        };
    }

    /**
     * Relationship with ReasonTransactions.
     * This assumes you’ve added `status_id` to the `reason_transactions` table.
     */
    public function reasonTransactions()
    {
        return $this->hasMany(ReasonTransaction::class, 'status_id');
    }

    /**
     * Relationship with OfficeLeaveRequest.
     */
    public function officeLeaveRequests()
    {
        return $this->hasMany(OfficeLeaveRequest::class, 'status', 'id');
    }
}

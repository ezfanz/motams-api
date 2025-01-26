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


    const MENUNGGU_SEMAKAN = 1;
    const DITERIMA_PENYEMAK = 2;
    const TIDAK_DITERIMA_PENYEMAK = 3;
    const DITERIMA_PENGESAH = 4;
    const TIDAK_DITERIMA_PENGESAH = 5;

    public static function getStatusName(int $statusId): string
    {
        $statusNames = [
            self::MENUNGGU_SEMAKAN => 'Menunggu Semakan',
            self::DITERIMA_PENYEMAK => 'Alasan Diterima Penyemak',
            self::TIDAK_DITERIMA_PENYEMAK => 'Alasan Tidak Diterima Penyemak',
            self::DITERIMA_PENGESAH => 'Alasan Diterima Pengesah',
            self::TIDAK_DITERIMA_PENGESAH => 'Alasan Tidak Diterima Pengesah',
        ];

        return $statusNames[$statusId] ?? 'Status Tidak Diketahui';
    }

    public static function getStatusColor(int $statusId): string
    {
        return match ($statusId) {
            self::DITERIMA_PENGESAH => '#28a745', // Green
            self::DITERIMA_PENYEMAK => '#17a2b8', // Blue
            self::MENUNGGU_SEMAKAN,
            self::TIDAK_DITERIMA_PENYEMAK,
            self::TIDAK_DITERIMA_PENGESAH => '#ffc107', // Yellow
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

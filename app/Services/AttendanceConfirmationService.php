<?php

namespace App\Services;

use App\Models\TransAlasan;
use App\Models\Status;
use Carbon\Carbon;

class AttendanceConfirmationService
{
    public function getConfirmationDetails(int $id): ?array
    {
        $transaction = TransAlasan::with([
            'user:id,fullname,jawatan,department_id',
            'user.department:id,diskripsi',
            'jenisAlasan:id,diskripsi_bm',
            'alasan:id,diskripsi',
            'penyemak:id,fullname', // Corrected from 'reviewer' to 'penyemak'
            'status:id,diskripsi',
        ])
        ->where('id', $id)
        ->where('is_deleted', '!=', 1)
        ->first();
    
        if (!$transaction) {
            return null;
        }
    
        return [
            'fullname' => $transaction->user->fullname ?? '',
            'jawatan' => $transaction->user->jawatan ?? '',
            'bahagian' => $transaction->user->department->diskripsi ?? '',
            'tarikh' => Carbon::parse($transaction->log_datetime)->format('d/m/Y'),
            'hari' => Carbon::parse($transaction->log_datetime)->isoFormat('dddd'),
            'jenis_alasan' => $transaction->jenisAlasan->diskripsi_bm ?? '',
            'sebab_alasan' => $transaction->alasan->diskripsi ?? '',
            'catatan_pegawai' => $transaction->catatan_peg,
            'tarikh_penyelarasan' => $transaction->tkh_peg_alasan
                ? Carbon::parse($transaction->tkh_peg_alasan)->format('d/m/Y h:i:s A')
                : '',
            'pegawai_semakan' => $transaction->penyemak->fullname ?? '', // Corrected from 'reviewer' to 'penyemak'
            'catatan_pegawai_semakan' => $transaction->catatan_penyemak ?? '',
            'status_semakan' => $transaction->status->diskripsi ?? '',
            'tarikh_semakan' => $transaction->tkh_penyemak_semak
                ? Carbon::parse($transaction->tkh_penyemak_semak)->format('d/m/Y h:i:s A')
                : '',
            'status_pengesahan' => Status::where('is_deleted', '!=', 1)
                ->whereIn('id', [4, 5, 6])
                ->get(['id', 'diskripsi']),
        ];
    }

    public function processConfirmation(array $data, int $userId): array
    {
        $transaction = TransAlasan::find($data['tralasan_id']);

        if (!$transaction) {
            return ['status' => false, 'message' => 'Transaction not found.'];
        }

        $transaction->update([
            'pengesah_id' => $userId,
            'status_pengesah' => $data['status_pengesah'],
            'catatan_pengesah' => $data['catatan_pengesah'],
            'tkh_pengesah_sah' => Carbon::now(),
            'status' => $data['status_pengesah'],
        ]);

        $message = match ($data['status_pengesah']) {
            4 => 'Proses kemaskini rekod berjaya',
            5 => 'Proses kemaskini rekod berjaya.',
            6 => 'Proses kemaskini rekod berjaya.',
            default => 'Proses kemaskini rekod berjaya',
        };

        return ['status' => true, 'message' => $message];
    }
}

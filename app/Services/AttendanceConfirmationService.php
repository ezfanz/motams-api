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
            'reviewer:id,fullname',
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
            'pegawai_semakan' => $transaction->reviewer->fullname ?? '',
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
        $transaction = TransAlasan::find($data['transid']);

        if (!$transaction) {
            return ['status' => false, 'message' => 'Transaction not found.'];
        }

        $transaction->update([
            'pengesah_id' => $userId,
            'status_pengesah' => $data['statusalasan'],
            'catatan_pengesah' => $data['catatanpengesah'],
            'tkh_pengesah_sah' => Carbon::now(),
            'status' => $data['statusalasan'],
        ]);

        $message = match ($data['statusalasan']) {
            4 => 'Proses kemaskini rekod berjaya dan makluman pengesahan telah dihantar ke Pegawai Seliaan.',
            5 => 'Proses kemaskini rekod berjaya dan telah dihantar semula ke Pegawai Seliaan untuk tindakan selanjutnya.',
            6 => 'Proses kemaskini rekod berjaya dan telah dihantar semula ke Pegawai Seliaan untuk tindakan selanjutnya.',
            default => 'Kemaskini tidak diketahui.',
        };

        return ['status' => true, 'message' => $message];
    }
}

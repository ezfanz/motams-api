<?php

namespace App\Repositories;

use App\Models\TransAlasan;
use Carbon\Carbon;

class AttendanceActionRepository
{
    public function getAttendanceStatus($userId, $datetime, $jenisAlasanId)
    {
        return TransAlasan::select(
            'trans_alasan.status as status_code',
            'alasan.diskripsi as reason'
        )
            ->leftJoin('alasan', 'trans_alasan.alasan_id', '=', 'alasan.id')
            ->where('trans_alasan.idpeg', $userId)
            ->where('trans_alasan.log_datetime', $datetime)
            ->where('trans_alasan.jenisalasan_id', $jenisAlasanId)
            ->first();
    }

    public function createRecord(array $data, int $jenisAlasanId)
    {
        $logDatetime = $data['datetimein'] ?? $data['datetimeout'] ?? $data['fulldate'] ?? null;

        if (!$logDatetime) {
            throw new \InvalidArgumentException('Missing log datetime for the record.');
        }

        TransAlasan::create([
            'idpeg' => $data['idpeg'],
            'log_datetime' => $logDatetime,
            'alasan_id' => $data['statalasan'],
            'jenisalasan_id' => $jenisAlasanId,
            'catatan_peg' => $data['catatanpeg'],
            'status' => 1, // Pending Adjustment
            'id_pencipta' => auth()->id(),
            'tkh_peg_alasan' => Carbon::now(),
        ]);
    }

    public function updateRecord(array $data, int $jenisAlasanId)
    {
        $trans = TransAlasan::find($data['transid']);

        if ($trans) {
            $trans->update([
                'alasan_id' => $data['statalasan'],
                'catatan_peg' => $data['catatanpeg'],
                'status' => 1, // Pending Adjustment
                'id_pencipta' => auth()->id(),
                'tkh_peg_alasan' => Carbon::now(),
            ]);
        }
    }
}

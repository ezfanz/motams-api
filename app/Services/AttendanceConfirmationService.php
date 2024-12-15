<?php

namespace App\Services;

use App\Models\ReasonTransaction;
use App\Models\Status;
use Carbon\Carbon;

class AttendanceConfirmationService
{
    public function getConfirmationDetails(int $id): ?array
    {
        $transaction = ReasonTransaction::with(['employee', 'reasonType', 'reason', 'reviewer', 'status'])
            ->where('id', $id)
            ->first();

        if (!$transaction) {
            return null;
        }

        return [
            'fullname' => $transaction->employee->name ?? '',
            'jawatan' => $transaction->employee->position ?? '',
            'bahagian' => $transaction->employee->department->name ?? '',
            'tarikh' => $transaction->log_timestamp->format('d/m/Y'),
            'hari' => $transaction->log_timestamp->isoFormat('dddd'),
            'jenis_alasan' => $transaction->reasonType->description ?? '',
            'sebab_alasan' => $transaction->reason->description ?? '',
            'catatan_pegawai' => $transaction->employee_notes,
            'tarikh_penyelarasan' => $transaction->employee_reason_at
                ? $transaction->employee_reason_at->format('d/m/Y h:i:s A')
                : '',
            'pegawai_semakan' => $transaction->reviewer->name ?? '',
            'catatan_pegawai_semakan' => $transaction->review_notes ?? '',
            'status_semakan' => $transaction->status->description ?? '',
            'tarikh_semakan' => $transaction->reviewed_at
                ? $transaction->reviewed_at->format('d/m/Y h:i:s A')
                : '',
            'status_pengesahan' => Status::whereNull('deleted_at')
                ->whereIn('id', [4, 5, 6])
                ->get(['id', 'description']),
        ];
    }

    public function processConfirmation(array $data, int $userId): array
    {
        $transaction = ReasonTransaction::find($data['transid']);

        if (!$transaction) {
            return ['status' => false, 'message' => 'Transaction not found.'];
        }

        $transaction->update([
            'approved_by' => $userId,
            'approval_status' => $data['statusalasan'],
            'approval_notes' => $data['catatanpengesah'],
            'approved_at' => Carbon::now(),
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

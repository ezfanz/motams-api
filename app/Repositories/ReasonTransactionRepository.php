<?php

namespace App\Repositories;

use App\Models\TransAlasan;

class ReasonTransactionRepository
{
    /**
     * Find a transaction by ID.
     */
    public function findById(int $id): ?TransAlasan
    {
        return TransAlasan::with([
            'user:id,fullname,jawatan,department_id',
            'user.department:id,diskripsi',
            'jenisAlasan:id,diskripsi_bm',
            'alasan:id,diskripsi',
        ])
        ->where('is_deleted', '!=', 1)
        ->find($id);
    }

     /**
     * Update the review for a transaction.
     */
    public function updateReview(int $id, array $data): bool
    {
        $transaction = TransAlasan::find($id);

        if (!$transaction) {
            return false;
        }

        return $transaction->update($data);
    }

    public function findByIds(array $ids)
    {
        return TransAlasan::whereIn('id', $ids)->get();
    }

    public function updateBatchReviewStatus(array $recordIds, int $reviewStatusId, ?string $reviewNotes)
    {
        return TransAlasan::whereIn('id', $recordIds)->update([
            'id' => $reviewStatusId,
            'catatan_peg' => $reviewNotes,
            'updated_at' => now(),
        ]);
    }
    
    

}

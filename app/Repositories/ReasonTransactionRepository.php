<?php

namespace App\Repositories;

use App\Models\ReasonTransaction;

class ReasonTransactionRepository
{
    /**
     * Find a transaction by ID.
     */
    public function findById(int $id): ?ReasonTransaction
    {
        return ReasonTransaction::with([
            'employee',           // Employee relationship
            'reasonType',         // ReasonType relationship
            'reason',             // Reason relationship
            'reviewer',           // Reviewer relationship
            'approver',           // Approver relationship
            'creator',            // Creator relationship
            'reasonable',         // Polymorphic reasonable relationship
        ])->find($id);
    }

    /**
     * Update the review for a transaction.
     */
    public function updateReview(int $id, array $data): bool
    {
        $transaction = ReasonTransaction::find($id);

        if (!$transaction) {
            return false;
        }

        return $transaction->update($data);
    }

    public function findByIds(array $ids)
    {
        return ReasonTransaction::whereIn('id', $ids)->get();
    }

}

<?php

namespace App\Repositories;

use App\Models\Status;

class StatusRepository
{
    /**
     * Get all statuses from the database.
     */
    public function getAllStatuses()
    {
        return Status::all();
    }

     /**
     * Get filtered statuses based on the user's role.
     */
    public function getStatusesByRole(?int $roleId)
    {
        if (!$roleId) {
            return [];
        }

        // Define roles for Penyemak (Reviewer)
        $reviewerRoles = [5, 7, 8, 10, 11, 15, 17];

        // Define roles for Pengesah (Approver)
        $approverRoles = [6, 7, 9, 10, 12, 16, 17];

        if (in_array($roleId, $reviewerRoles)) {
            // Return statuses for Semakan (Review)
            return Status::where('is_deleted', '!=', 1)
                ->whereIn('id', [Status::DITERIMA_PENYEMAK, Status::TIDAK_DITERIMA_PENYEMAK, Status::MEMERLUKAN_MAKLUMAT_LANJUT])
                ->orderBy('id', 'asc')
                ->get();
        }

        if (in_array($roleId, $approverRoles)) {
            // Return statuses for Pengesahan (Approval)
            return Status::where('is_deleted', '!=', 1)
                ->whereIn('id', [Status::DITERIMA_PENGESAH, Status::TIDAK_DITERIMA_PENGESAH, Status::MEMERLUKAN_MAKLUMAT_LANJUT])
                ->orderBy('id', 'asc')
                ->get();
        }

        // If the user is not in Semakan or Pengesahan role, return all statuses
        return Status::where('is_deleted', '!=', 1)->orderBy('id', 'asc')->get();
    }

      /**
     * Get Semakan (Review) statuses.
     */
    public function getSemakanStatuses()
    {
        return Status::where('is_deleted', '!=', 1)
            ->whereIn('id', [Status::DITERIMA_PENYEMAK, Status::TIDAK_DITERIMA_PENYEMAK, Status::MEMERLUKAN_MAKLUMAT_LANJUT])
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Get Pengesahan (Approval) statuses.
     */
    public function getPengesahanStatuses()
    {
        return Status::where('is_deleted', '!=', 1)
            ->whereIn('id', [Status::DITERIMA_PENGESAH, Status::TIDAK_DITERIMA_PENGESAH, Status::MEMERLUKAN_MAKLUMAT_LANJUT])
            ->orderBy('id', 'asc')
            ->get();
    }
    
}

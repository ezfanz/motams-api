<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status_id' => $this->status_id,
            'date' => $this->date,
            'reason' => $this->reason,
            'created_by' => $this->createdByUser ? $this->createdByUser->name : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'attendance_status' => $this->status,
            'details' => $this->details,
        ];
    }
}

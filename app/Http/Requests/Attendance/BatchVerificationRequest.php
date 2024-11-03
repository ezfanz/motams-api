<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class BatchVerificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'record_ids' => 'required|array',
            'record_ids.*' => 'exists:attendance_records,id',
            'verification_status_id' => 'required|exists:review_statuses,id',
            'verification_notes' => 'nullable|string',
        ];
    }
}

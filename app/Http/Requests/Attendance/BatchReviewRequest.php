<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class BatchReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'record_ids' => 'required|array|min:1',
            'record_ids.*' => 'integer|exists:attendance_records,id', // Check each ID exists in attendance_records
            'review_status_id' => 'required|exists:review_statuses,id', // Ensure the review status ID exists
            'review_notes' => 'nullable|string|max:1000'
        ];
    }
}

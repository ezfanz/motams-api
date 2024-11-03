<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class BatchApprovalRequest extends FormRequest
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
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => 'exists:office_leave_requests,id', // Each ID should exist in the office_leave_requests table
            'approval_status_id' => 'required|exists:review_statuses,id',
            'approval_notes' => 'nullable|string',
        ];
    }

    /**
     * Customize the validation messages.
     */
    public function messages(): array
    {
        return [
            'request_ids.required' => 'At least one leave request must be selected.',
            'approval_status_id.required' => 'Please select an approval status.',
        ];
    }
}

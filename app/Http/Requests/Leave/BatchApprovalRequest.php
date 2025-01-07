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
            'leave_id' => 'required|array|min:1', // Must be an array with at least one element
            'leave_id.*' => 'integer|exists:office_leave_requests,id', // Each ID must exist in the table
            'statusalasan' => 'required|integer|in:16,17', // Must be 16 (approved) or 17 (rejected)
            'catatanpengesah' => 'nullable|string|max:255', // Optional notes, max 255 characters
        ];
    }

    /**
     * Customize the validation messages.
     */
    public function messages(): array
    {
        return [
            'leave_id.required' => 'Please select at least one leave request.',
            'leave_id.array' => 'Leave IDs must be provided as an array.',
            'leave_id.min' => 'At least one leave ID must be selected.',
            'leave_id.*.exists' => 'One or more selected leave requests do not exist.',
            'statusalasan.required' => 'Approval status is required.',
            'statusalasan.in' => 'Approval status must be 16 (Approved) or 17 (Rejected).',
            'catatanpengesah.max' => 'Approval notes must not exceed 255 characters.',
        ];
    }
}

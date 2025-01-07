<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class OfficeLeaveApprovalRequest extends FormRequest
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
            'leave_id' => 'required|integer|exists:office_leave_requests,id,status,15',
            'statusalasan' => 'required|integer|in:16,17',
            'catatanpengesah' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'leave_id.required' => 'A leave request ID is required.',
            'leave_id.integer' => 'Leave ID must be an integer.',
            'leave_id.exists' => 'The selected leave request is invalid or not in a pending state.',
            'statusalasan.required' => 'An approval status is required.',
            'statusalasan.in' => 'Approval status must be either 16 (Approved) or 17 (Rejected).',
            'catatanpengesah.max' => 'Approval notes cannot exceed 255 characters.',
        ];
    }
}

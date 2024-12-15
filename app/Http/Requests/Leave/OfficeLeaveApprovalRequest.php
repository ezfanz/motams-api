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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'leave_ids' => 'required|array',
            'leave_ids.*' => 'integer|exists:office_leave_requests,id',
            'status' => 'required|integer|in:16,17',
            'approval_notes' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'leave_ids.required' => 'Leave IDs are required.',
            'leave_ids.array' => 'Leave IDs must be an array.',
            'leave_ids.*.exists' => 'One or more leave requests are invalid.',
            'status.required' => 'Approval status is required.',
            'status.in' => 'Approval status must be 16 (Approved) or 17 (Rejected).',
        ];
    }
}

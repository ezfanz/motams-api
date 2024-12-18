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
            'leave_id' => 'required|array|min:1',
            'leave_id.*' => 'integer|exists:office_leave_requests,id',
            'statusalasan' => 'required|integer|in:16,17',
            'catatanpengesah' => 'nullable|string|max:255',
        ];
    }

    /**
     * Customize the validation messages.
     */
    public function messages(): array
    {
        return [
            'leave_id.required' => 'At least one leave request must be selected.',
            'statusalasan.required' => 'Please provide a status for approval.',
        ];
    }
}

<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class BatchApproveRequest extends FormRequest
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
    public function rules()
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:reason_transactions,id',
            'status' => 'required|integer|in:4,5,6', // Example: Approved, Rejected, More Info Needed
            'notes' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'ids.required' => 'Please select at least one record.',
            'status.required' => 'Please provide a status for the approval.',
        ];
    }
}

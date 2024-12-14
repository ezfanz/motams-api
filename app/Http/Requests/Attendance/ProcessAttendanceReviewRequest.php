<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class ProcessAttendanceReviewRequest extends FormRequest
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
            'review_id' => 'required|integer|exists:reason_transactions,id',
            'status' => 'required|integer|in:2,3,6',
            'notes' => 'nullable|string|max:255',
        ];
    }

     /**
     * Custom error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'review_id.required' => 'The review ID is required.',
            'review_id.integer' => 'The review ID must be an integer.',
            'review_id.exists' => 'The selected review ID is invalid.',
            'status.required' => 'The status is required.',
            'status.integer' => 'The status must be an integer.',
            'status.in' => 'The status must be one of the following values: 2, 3, 6.',
            'notes.string' => 'The notes must be a string.',
            'notes.max' => 'The notes must not exceed 255 characters.',
        ];
    }
}

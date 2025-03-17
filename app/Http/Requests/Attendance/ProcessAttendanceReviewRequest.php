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
            'tralasan_id' => 'required|integer|exists:trans_alasan,id',
            'status_penyemak' => 'required|integer|in:2,3,6',
            'catatan_penyemak' => 'nullable|string|max:255',
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
            'tralasan_id.required' => 'The tralasan_id is required.',
            'tralasan_id.integer' => 'The tralasan_id must be an integer.',
            'tralasan_id.exists' => 'The selected tralasan_id is invalid.',
            'status.required' => 'The status is required.',
            'status.integer' => 'The status must be an integer.',
            'status.in' => 'The status must be one of the following values: 2, 3, 6.',
            'notes.string' => 'The notes must be a string.',
            'notes.max' => 'The notes must not exceed 255 characters.',
        ];
    }
}

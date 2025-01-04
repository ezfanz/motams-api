<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class BatchProcessAttendanceReviewRequest extends FormRequest
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
            'tralasan_ids' => 'required|array|min:1',
            'tralasan_ids.*' => 'integer|exists:trans_alasan,id',
            'status' => 'required|integer|in:2,3,6',
            'notes' => 'nullable|string|max:255',
        ];
    }
}

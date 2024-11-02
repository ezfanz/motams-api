<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceReviewIndexRequest extends FormRequest
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
            'status' => 'nullable|string|exists:review_statuses,status', // Check that status exists in review_statuses table
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:1900|max:' . now()->year
        ];
    }
}

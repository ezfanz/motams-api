<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfficeLeaveRequestRequest extends FormRequest
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
            'leave_type_id' => [
                'required',
                'exists:leave_types,id'
            ],
            'date_mula' => [
                'required',
                'after_or_equal:today' 
            ],
            'date_tamat' => [
                'required',
                'after_or_equal:today' 
            ],
            'day' => [
                'required',
            ],
            'start_time' => [
                'required',
                'date_format:H:i'
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time'
            ],
            'reason' => [
                'nullable',
                'string',
                'max:255' // Restrict reason length to 255 characters
            ],
            'status' => [
                'nullable',
                'integer',
                Rule::in([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17]) // Restrict to valid statuses
            ]
        ];
    }

    /**
     * Custom error messages for validation failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'leave_type_id.required' => 'The leave type is required.',
            'leave_type_id.exists' => 'The selected leave type does not exist.',
            'date.required' => 'The date of leave is required.',
            'date.date' => 'The date must be a valid date.',
            'date.after_or_equal' => 'The leave date cannot be in the past.',
            'day.required' => 'The day of the week is required.',
            'day.in' => 'The day must be a valid day of the week.',
            'start_time.required' => 'The start time is required.',
            'start_time.date_format' => 'The start time must be in the format HH:MM.',
            'end_time.required' => 'The end time is required.',
            'end_time.date_format' => 'The end time must be in the format HH:MM.',
            'end_time.after' => 'The end time must be after the start time.',
            'reason.max' => 'The reason cannot exceed 255 characters.',
            'status.in' => 'The status must be follow status table.'
        ];
    }
}

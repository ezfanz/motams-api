<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class OfficeLeaveRequestFormRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on auth rules
    }

    public function rules()
    {
        return [
            'jenis' => 'required|integer|in:1,2', // 1: Bekerja Luar Pejabat, 2: Time-Off
            'start_date' => 'required|date', // Renamed from tkh_mula to start_date for consistency
            'end_date' => 'required_if:jenis,1|nullable|date|after_or_equal:start_date', // Only required for "Bekerja Luar Pejabat"
            'day' => 'required_if:jenis,2|string|max:255', // Only required for "Time-Off"
            'start_time' => 'required_if:jenis,2|date_format:H:i', // Only required for "Time-Off"
            'end_time' => 'required_if:jenis,2|date_format:H:i|after:start_time', // Ensure end_time is after start_time
            'total_days' => 'nullable|numeric|min:1', // Total days for "Bekerja Luar Pejabat"
            'total_hours' => 'nullable|numeric|min:0', // Total hours for "Time-Off"
            'reason' => 'required|string|max:255' // Reason for leave is mandatory
        ];
    }

    public function messages()
    {
        return [
            'jenis.required' => 'The leave type is required.',
            'jenis.integer' => 'The leave type must be a valid integer.',
            'jenis.in' => 'The leave type must be either 1 (Bekerja Luar Pejabat) or 2 (Time-Off).',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.required_if' => 'The end date is required for "Bekerja Luar Pejabat".',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'day.required_if' => 'The day is required for "Time-Off".',
            'day.string' => 'The day must be a valid string.',
            'start_time.required_if' => 'The start time is required for "Time-Off".',
            'start_time.date_format' => 'The start time must be in the format HH:mm.',
            'end_time.required_if' => 'The end time is required for "Time-Off".',
            'end_time.date_format' => 'The end time must be in the format HH:mm.',
            'end_time.after' => 'The end time must be after the start time.',
            'total_days.numeric' => 'The total days must be a valid number.',
            'total_days.min' => 'The total days must be at least 1.',
            'total_hours.numeric' => 'The total hours must be a valid number.',
            'total_hours.min' => 'The total hours must be at least 0.',
            'reason.required' => 'The reason for leave is required.',
            'reason.string' => 'The reason must be a valid string.',
            'reason.max' => 'The reason must not exceed 255 characters.',
        ];
    }
}

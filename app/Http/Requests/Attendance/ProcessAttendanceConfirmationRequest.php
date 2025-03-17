<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class ProcessAttendanceConfirmationRequest extends FormRequest
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
            'status_pengesah' => 'required|integer|in:4,5,6',
            'catatan_pengesah' => 'nullable|string|max:255',
        ];
    }
}

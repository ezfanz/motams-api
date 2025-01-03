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
            'transid' => 'required|integer|exists:trans_alasan,id',
            'statusalasan' => 'required|integer|in:4,5,6',
            'catatanpengesah' => 'nullable|string|max:255',
        ];
    }
}

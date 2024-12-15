<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class GetLeaveStatusRequest extends FormRequest
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
            'pegawai_id' => 'nullable|integer|exists:users,id',
            'month_start' => 'nullable|date_format:Y-m',
            'month_end' => 'nullable|date_format:Y-m',
        ];
    }

    /**
     * Custom messages for validation errors (optional).
     */
    public function messages(): array
    {
        return [
            'pegawai_id.exists' => 'The selected employee does not exist.',
            'month_start.date_format' => 'The month_start must be in the format YYYY-MM.',
            'month_end.date_format' => 'The month_end must be in the format YYYY-MM.',
        ];
    }
}

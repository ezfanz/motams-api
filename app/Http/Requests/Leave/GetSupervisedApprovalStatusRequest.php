<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class GetSupervisedApprovalStatusRequest extends FormRequest
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
            'pegawai_id' => 'nullable|integer|exists:users,id',
            'month_start' => 'nullable|date_format:Y-m',
            'month_end' => 'nullable|date_format:Y-m|after_or_equal:month_start',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'pegawai_id.integer' => 'The selected Pegawai ID must be a valid number.',
            'pegawai_id.exists' => 'The selected Pegawai does not exist in the system.',
            'month_start.date_format' => 'The "Dari" field must follow the format YYYY-MM.',
            'month_end.date_format' => 'The "Hingga" field must follow the format YYYY-MM.',
            'month_end.after_or_equal' => 'The "Hingga" date must be after or equal to the "Dari" date.',
        ];
    }
}

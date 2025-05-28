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
            'leave_type_id.required' => 'Jenis cuti adalah wajib.',
            'leave_type_id.exists' => 'Jenis cuti yang dipilih tidak wujud.',

            'date_mula.required' => 'Tarikh mula adalah wajib.',
            'date_mula.after_or_equal' => 'Tarikh mula mestilah hari ini atau selepas hari ini.',

            'date_tamat.required' => 'Tarikh tamat adalah wajib.',
            'date_tamat.after_or_equal' => 'Tarikh tamat mestilah hari ini atau selepas hari ini.',

            'day.required' => 'Hari adalah wajib.',

            'start_time.required' => 'Masa keluar adalah wajib.',
            'start_time.date_format' => 'Format masa keluar mestilah dalam format HH:MM.',

            'end_time.required' => 'Masa kembali adalah wajib.',
            'end_time.date_format' => 'Format masa kembali mestilah dalam format HH:MM.',
            'end_time.after' => 'Masa kembali mestilah selepas masa keluar.',

            'reason.max' => 'Catatan tidak boleh melebihi 255 aksara.',

            'status.in' => 'Status mesti sah mengikut senarai status yang dibenarkan.'
        ];
    }
    
}

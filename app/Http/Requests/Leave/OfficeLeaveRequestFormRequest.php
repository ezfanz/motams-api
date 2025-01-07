<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class OfficeLeaveRequestFormRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on auth rules
    }

   /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'jenis' => 'required|integer|exists:leave_types,id',
            'tkh_mula' => 'required|date',
            'tkh_hingga' => 'required_if:jenis,1|date|nullable',
            'hari_timeoff' => 'required_if:jenis,2|string|nullable',
            'masa_keluar' => 'required_if:jenis,2|date_format:H:i|nullable',
            'masa_kembali' => 'required_if:jenis,2|date_format:H:i|nullable',
            'bilhari' => 'required_if:jenis,1|numeric|nullable',
            'catatan' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis cuti diperlukan.',
            'tkh_mula.required' => 'Tarikh mula diperlukan.',
            'tkh_hingga.required_if' => 'Tarikh hingga diperlukan untuk Bekerja Luar Pejabat.',
            'hari_timeoff.required_if' => 'Hari diperlukan untuk Time-Off.',
            'masa_keluar.required_if' => 'Masa keluar diperlukan untuk Time-Off.',
            'masa_kembali.required_if' => 'Masa kembali diperlukan untuk Time-Off.',
            'bilhari.required_if' => 'Bilangan hari diperlukan untuk Bekerja Luar Pejabat.',
            'catatan.required' => 'Sebab diperlukan.',
        ];
    }
}

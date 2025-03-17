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
            'masa_keluar' => 'required|date_format:H:i:s',
            'masa_kembali' => 'required|date_format:H:i:s|after:masa_keluar',
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
            'masa_keluar.regex' => 'The masa keluar field must match the format Y-m-d H:i:s (e.g., 08:30).',
            'masa_kembali.regex' => 'The masa kembali field must match the format Y-m-d H:i:s (e.g., 17:00).',
            'bilhari.required_if' => 'Bilangan hari diperlukan untuk Bekerja Luar Pejabat.',
            'catatan.required' => 'Sebab diperlukan.',
        ];
    }
    
}

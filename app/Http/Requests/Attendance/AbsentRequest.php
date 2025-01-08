<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AbsentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Add your authorization logic if needed
    }

    public function rules()
    {
        return [
            'idpeg' => 'required|integer',
            'fulldate' => 'required|date',
            'statalasan' => 'nullable|integer',
            'catatanpeg' => 'nullable|string',
            'transid' => 'nullable|integer', // For updates
        ];
    }

    public function messages()
    {
        return [
            'idpeg.required' => 'Sila masukkan ID pengguna.',
            'fulldate.required' => 'Sila masukkan tarikh.',
        ];
    }
}

<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class LateArrivalRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization logic if needed
    }

    public function rules()
    {
        return [
            'idpeg' => 'required|integer|exists:users,id',
            'fulldate' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Validate both 'd/m/Y' and 'Y-m-d' formats
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                        $fail('Tarikh tidak sah. Format yang diterima: d/m/Y atau Y-m-d.');
                    }
                }
            ],
            'datetimein' => 'required|date_format:Y-m-d H:i:s', // Ensure proper datetime format
            'statalasan' => 'required_if:statuslate,null|integer|exists:alasan,id',
            'catatanpeg' => 'required_if:statuslate,null|string|max:500',
            'statuslate' => 'nullable|integer',
            'transid' => 'required_if:statuslate,1,3,5|integer|exists:trans_alasan,id'
        ];
    }

    public function messages()
    {
        return [
            'fulldate.required' => 'Sila masukkan tarikh.',
            'fulldate.regex' => 'Tarikh tidak sah. Format yang diterima: d/m/Y atau Y-m-d.',
            'datetimein.required' => 'Sila masukkan masa kehadiran.',
            'datetimein.date_format' => 'Masa kehadiran tidak sah. Format yang diterima: Y-m-d H:i:s.',
            'statalasan.required_if' => 'Sila masukkan Sebab / Alasan',
            'catatanpeg.required_if' => 'Sila masukkan Catatan Pegawai',
            'transid.required_if' => 'Trans ID is required for editing records.'
        ];
    }
}

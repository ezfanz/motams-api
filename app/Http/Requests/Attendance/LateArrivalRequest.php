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
            'datetimein' => 'required|date',
            'statalasan' => 'required_if:statuslate,null|integer|exists:alasan,id',
            'catatanpeg' => 'required_if:statuslate,null|string|max:500',
            'statuslate' => 'nullable|integer',
            'transid' => 'required_if:statuslate,1,3,5|integer|exists:trans_alasan,id'
        ];
    }

    public function messages()
    {
        return [
            'statalasan.required_if' => 'Sila masukkan Sebab / Alasan',
            'catatanpeg.required_if' => 'Sila masukkan Catatan Pegawai',
            'transid.required_if' => 'Trans ID is required for editing records.'
        ];
    }
}

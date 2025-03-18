<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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
            'fulldate' => [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        // Attempt to parse and reformat the date
                        $parsedDate = Carbon::parse($value);
                        $this->merge(['fulldate' => $parsedDate->format('Y-m-d')]);
                    } catch (\Exception $e) {
                        $fail('Format tarikh tidak sah. Gunakan format d/m/Y atau Y-m-d.');
                    }
                }
            ],
            'statalasan' => 'nullable|integer',
            'catatanpeg' => 'nullable|string|max:500',
            'transid' => 'nullable|integer', // For updates
        ];
    }

    public function messages()
    {
        return [
            'idpeg.required' => 'Sila masukkan ID pengguna.',
            'fulldate.required' => 'Sila masukkan tarikh.',
            'fulldate.date' => 'Format tarikh tidak sah. Gunakan format d/m/Y atau Y-m-d.',
        ];
    }
}

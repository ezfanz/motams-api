<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Status;

class AttendanceReviewIndexRequest extends FormRequest
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
            'status' => 'nullable|integer|in:' . implode(',', [
                Status::MENUNGGU_SEMAKAN,
                Status::DITERIMA_PENYEMAK,
                Status::TIDAK_DITERIMA_PENYEMAK,
                Status::DITERIMA_PENGESAH,
                Status::TIDAK_DITERIMA_PENGESAH,
            ]),
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:1900|max:' . now()->year,
        ];
    }
}

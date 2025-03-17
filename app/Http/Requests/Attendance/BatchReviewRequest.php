<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class BatchReviewRequest extends FormRequest
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
            'tralasan_id' => 'required|array|min:1',
            'tralasan_id.*' => 'integer|exists:trans_alasan,id',
            'status_penyemak' => 'required|integer|in:2,3,6',
            'catatan_penyemak' => 'nullable|string|max:500',
        ];
    }
}

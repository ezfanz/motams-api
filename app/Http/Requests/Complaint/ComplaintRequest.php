<?php

namespace App\Http\Requests\Complaint;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintRequest extends FormRequest
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
    public function rules()
    {
        return [
            'date' => 'required|date',
            'day' => 'required|string|max:50',
            'complaint_type' => 'required|string|max:100',
            'complaint_title' => 'required|string|max:255',
            'officer_notes' => 'nullable|string',
            'status' => 'required|in:Pending,Resolved,Closed',
            'submitted_by' => 'nullable|exists:users,id',
            'handled_by' => 'nullable|exists:users,id',
        ];
    }
}

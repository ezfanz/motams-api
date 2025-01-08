<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class EarlyDepartureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add authorization logic if required
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'idpeg' => 'required|integer|exists:users,id',
            'datetimeout' => 'required|date',
            'statalasan' => 'required_if:statusearly,1|required_if:statusearly,3|integer|exists:alasan,id',
            'catatanpeg' => 'required_if:statusearly,1|required_if:statusearly,3|string|max:255',
            'statusearly' => 'nullable|integer|in:1,2,3,4,5', // Valid status codes
            'transid' => 'nullable|integer|exists:trans_alasan,id', // Only required for edits
        ];
    }

    /**
     * Get custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'idpeg.required' => 'The user ID is required.',
            'idpeg.exists' => 'The user ID must exist in the users table.',
            'datetimeout.required' => 'The date and time are required.',
            'datetimeout.date' => 'The date and time must be a valid date.',
            'statalasan.required_if' => 'The reason is required for this action.',
            'statalasan.exists' => 'The selected reason does not exist.',
            'catatanpeg.required_if' => 'Remarks are required for this action.',
            'catatanpeg.max' => 'Remarks may not exceed 255 characters.',
            'statusearly.in' => 'The status code is invalid.',
            'transid.exists' => 'The transaction ID must exist in the trans_alasan table.',
        ];
    }

    /**
     * Set box color based on `statusearly`.
     */
    public function getBoxColor(): string
    {
        $statusearly = $this->input('statusearly', null);

        switch ($statusearly) {
            case 4: // Green (Approved)
                return '#28a745';
            case 2: // Blue (Pending Verification)
                return '#17a2b8';
            case 1: // Yellow (Pending Adjustment)
            case 3: // Yellow (Needs Correction)
            case 5: // Yellow (Pending Finalization)
                return '#ffc107';
            default: // Red (Not Set)
                return '#dc3545';
        }
    }
}

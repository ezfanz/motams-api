<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            // Validate that the role_id exists in the roles table
            'role_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!Role::where('id', $value)->exists()) {
                        $fail('The selected role is invalid.');
                    }
                }
            ]
        ];
    }
}

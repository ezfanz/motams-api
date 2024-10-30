<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $this->route('user'),
            'password' => 'sometimes|confirmed|min:8',
            'role_id' => [
                'sometimes',
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

<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
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

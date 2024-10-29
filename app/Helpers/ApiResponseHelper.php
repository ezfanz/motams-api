<?php

namespace App\Helpers;

class ApiResponseHelper
{
    /**
     * Prepare user data with role name for API response.
     *
     * @param $user
     * @return array
     */
    public static function formatUserResponse($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            }), // Returns an array of role names
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];
    }
}

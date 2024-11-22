<?php

namespace App\Helpers;

class ApiResponseHelper
{
    /**
     * Prepare user data with role name and officer details for API response.
     *
     * @param $user
     * @return array
     */
    public static function formatUserResponse($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'position' => $user->position,
            'department' => $user->department,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'last_login_at' => $user->last_login_at ? $user->last_login_at->format('d/m/Y, h:i:s a') : null, // Format the date
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'reviewing_officer' => $user->reviewingOfficer ? [
                'id' => $user->reviewingOfficer->id,
                'name' => $user->reviewingOfficer->name,
            ] : null,
            'approving_officer' => $user->approvingOfficer ? [
                'id' => $user->approvingOfficer->id,
                'name' => $user->approvingOfficer->name,
            ] : null,
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            })
        ];
    }
}

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
  public static function formatUserResponse($user): array
{
    return [
        'id' => $user->id,
        'username' => $user->username,
        'email' => $user->email,
        'fullname' => $user->fullname,
        'nokp' => $user->nokp,
        'phone' => $user->phone,
        'unit' => $user->unit,
        'jawatan' => $user->jawatan,
        'gred' => $user->gred,
        'kump_khidmat' => $user->kump_khidmat,
        'role_id' => $user->role_id,
        'department_id' => $user->department_id,
        'telegram_id' => $user->telegram_id,
        'is_deleted' => $user->is_deleted,
        'last_login_at' => $user->last_login_at,
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at,
    ];
}
}

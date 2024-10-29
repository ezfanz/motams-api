<?php

namespace App\Repositories;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRepository
{
    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->save();

        return $user;
    }

    /**
     * Find a role by ID.
     *
     * @param int $roleId
     * @return Role|null
     */
    public function findRoleById(int $roleId): ?Role
    {
        return Role::find($roleId);
    }
}

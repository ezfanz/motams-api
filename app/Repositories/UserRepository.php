<?php

namespace App\Repositories;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRepository
{
    public function all()
    {
        return User::with('roles')->get();
    }

    public function find($id)
    {
        return User::with('roles')->findOrFail($id);
    }

    public function create(array $data): User
    {
        $user = new User;
        $user->name = $data['name'];
        $user->username = $data['username'] ?? null;
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->position = $data['position'] ?? null;
        $user->department = $data['department'] ?? null;
        $user->reviewing_officer_id = $data['reviewing_officer_id'] ?? null;
        $user->approving_officer_id = $data['approving_officer_id'] ?? null;
        $user->save();

        return $user;
    }

    public function update($id, array $data): User
    {
        $user = $this->find($id);
        $user->update($data);

        return $user;
    }

    public function delete($id)
    {
        $user = $this->find($id);
        $user->delete();

        return $user;
    }

    public function findRoleById(int $roleId): ?Role
    {
        return Role::find($roleId);
    }
}

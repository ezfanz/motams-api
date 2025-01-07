<?php

namespace App\Repositories;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

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

      /**
     * Fetch user profile data from the database.
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserProfile(int $userId): ?array
    {
        $result = DB::table('users')
            ->leftJoin('department', 'users.department_id', '=', 'department.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('users AS penyemak', 'users.penyemak_id', '=', 'penyemak.id')
            ->leftJoin('users AS pengesah', 'users.pengesah_id', '=', 'pengesah.id')
            ->select(
                'users.fullname',
                'users.jawatan',
                'department.diskripsi AS department',
                'roles.diskripsi AS role',
                'penyemak.fullname AS nama_penyemak',
                'pengesah.fullname AS nama_pengesah'
            )
            ->where('users.id', $userId)
            ->where('users.is_deleted', '!=', 1) // Ensuring soft delete handling
            ->first();

        return $result ? (array) $result : null; // Cast to array and return
    }
}

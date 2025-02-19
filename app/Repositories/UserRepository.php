<?php

namespace App\Repositories;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $user = User::with(['department', 'roles'])
            ->where('id', $userId)
            ->first();
    
        if (!$user) {
            return null;
        }
    
        // Debug: Log penyemak_id and pengesah_id to confirm they exist
        Log::info("User Profile Debug", [
            'userId' => $userId,
            'penyemak_id' => $user->penyemak_id,
            'pengesah_id' => $user->pengesah_id
        ]);
    
        return [
            'fullname' => $user->fullname,
            'jawatan' => $user->jawatan,
            'department' => $user->department->diskripsi ?? 'N/A',
            'role' => $user->roles->diskripsi ?? 'N/A',
            'nama_penyemak' => $this->getUserFullNameById($user->penyemak_id) ?? 'No Penyemak',
            'nama_pengesah' => $this->getUserFullNameById($user->pengesah_id) ?? 'No Pengesah',
        ];
    }
    
    public function getUserFullNameById(?int $userId): ?string
    {
        if (!$userId) {
            return null;
        }
    
        $user = User::withoutGlobalScope('is_deleted')
            ->where('id', $userId)
            ->value('fullname');
    
        Log::info("Fetching User Full Name", [
            'userId' => $userId,
            'fullname' => $user ?? 'NULL'
        ]);
    
        return $user ?? null;
    }


}

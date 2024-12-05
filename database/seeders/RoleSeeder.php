<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $reviewPermission = Permission::findOrCreate('review');
        $approvePermission = Permission::findOrCreate('approve');

        // Roles data from the provided image
        $roles = [
            ['id' => 1, 'name' => 'User'],
            ['id' => 2, 'name' => 'Pentadbir MOT'],
            ['id' => 3, 'name' => 'Sistem Admin'],
            ['id' => 4, 'name' => 'PTB'],
            ['id' => 5, 'name' => 'Penyemak'],
            ['id' => 6, 'name' => 'Pengesah'],
            ['id' => 7, 'name' => 'Penyemak / Pengesah'],
            ['id' => 8, 'name' => 'PTB / Penyemak'],
            ['id' => 9, 'name' => 'PTB / Pengesah'],
            ['id' => 10, 'name' => 'PTB / Penyemak / Pengesah'],
            ['id' => 11, 'name' => 'Pentadbir / Penyemak'],
            ['id' => 12, 'name' => 'Pentadbir / Pengesah'],
            ['id' => 13, 'name' => 'Pentadbir / Penyemak / Pengesah'],
            ['id' => 14, 'name' => 'PTB / Pentadbir / Penyemak'],
            ['id' => 15, 'name' => 'PTB / Pentadbir / Pengesah'],
            ['id' => 16, 'name' => 'PTB / Pentadbir / Penyemak / Pengesah'],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['id' => $roleData['id']], // Match by ID
                ['name' => $roleData['name']] // Update or set name
            );

            // Assign permissions based on role type
            if (str_contains($roleData['name'], 'Penyemak')) {
                $role->givePermissionTo($reviewPermission);
            }

            if (str_contains($roleData['name'], 'Pengesah')) {
                $role->givePermissionTo($approvePermission);
            }

            if (str_contains($roleData['name'], 'Penyemak / Pengesah')) {
                $role->givePermissionTo([$reviewPermission, $approvePermission]);
            }
        }
    }
}

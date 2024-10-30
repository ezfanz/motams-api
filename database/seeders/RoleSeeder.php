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

        // Assign permissions to roles
        $semakanRole = Role::findOrCreate('Semakan');
        $pengesahanRole = Role::findOrCreate('Pengesahan');
        $semakanPengesahanRole = Role::findOrCreate('Semakan & Pengesahan');

        $semakanRole->givePermissionTo($reviewPermission);
        $pengesahanRole->givePermissionTo($approvePermission);
        $semakanPengesahanRole->givePermissionTo([$reviewPermission, $approvePermission]);
    }
}

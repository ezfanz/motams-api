<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserMockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id' => 1,
            'username' => 'irfan',
            'email' => 'irfanzulkefly144@gmail.com',
            'password' => Hash::make('password'),
            'fullname' => 'Muhammad Irfan Zulkefly',
            'nokp' => '960928-10-5925',
            'phone' => '60193205891',
            'unit' => 'Admin Unit',
            'jawatan' => 'Kerani',
            'gred' => 'A1',
            'kump_khidmat' => 'MY',
            'ketua_jbtn' => true,
            'telegram_id' => '123456789',
            'encrypt' => null,
            'remember_token' => null,
            'role_id' => 1,
            'department_id' => 1,
        ]);
    }
}

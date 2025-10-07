<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // seeder admin
        $adminRole = Role::create([
            'name' => 'admin',
        ]);

        // seeder lender
        $lenderRole = Role::create([
            'name' => 'lender',
        ]);

        // seeder agend
        $agenRole = Role::create([
            'name' => 'agen',
        ]);

        $user = User::create([
            'name' => 'Team tedja',
            'email' => 'admintedja@gmail.com',
            'phone' => '08123456789',
            'photo' => 'default.png',
            'password' => bcrypt('password'),
        ]);
        
        $user->assignRole($adminRole);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleAndAdminSeeder extends Seeder
{
    public function run()
    {
        // create roles
        $roles = ['owner', 'admin', 'team-member', 'user'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // create admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@booking.com'
        ], [
            'name' => 'Site Admin',
            'password' => bcrypt('Admin@321'),
        ]);

        // assign admin role if not set
        if (! $admin->hasRole('owner')) {
            $admin->assignRole('owner');
        }
    }
}

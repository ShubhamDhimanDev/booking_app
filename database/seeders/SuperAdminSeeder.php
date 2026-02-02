<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates a configurable super-admin user and ensures the role exists.
     * Use environment variables `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD` to set credentials in non-development environments.
     *
     * @return void
     */
    public function run()
    {
        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@booking.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!');
        $roleName = 'super-admin';

        // Ensure role exists
        Role::firstOrCreate(['name' => $roleName]);

        // Create or update the super admin user
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
            ]
        );

        // Assign role if not already assigned
        if (! $user->hasRole($roleName)) {
            $user->assignRole($roleName);
        }
    }
}

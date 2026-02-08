<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Roles Structure for Multi-Tenant SaaS:
     * - super-admin: Platform administrator (access to all organizations)
     * - org-owner: Organization owner (full access to their organization)
     * - org-admin: Organization administrator (manage organization, limited access)
     * - org-member: Organization team member (create events, manage bookings)
     * - guest: External user (book events, view own bookings)
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Organization Management
            'view organizations',
            'create organizations',
            'edit organizations',
            'delete organizations',
            'manage organization settings',
            'view organization activity',

            // User Management
            'view users',
            'invite users',
            'edit users',
            'delete users',
            'assign roles',

            // Event Management
            'view events',
            'create events',
            'edit events',
            'delete events',
            'publish events',

            // Booking Management
            'view all bookings',
            'view own bookings',
            'create bookings',
            'edit bookings',
            'cancel bookings',
            'reschedule bookings',

            // Payment Management
            'view payments',
            'process refunds',
            'view payment reports',

            // Promo Code Management
            'view promo codes',
            'create promo codes',
            'edit promo codes',
            'delete promo codes',

            // Settings Management
            'view settings',
            'edit settings',

            // Calendar Integration
            'manage calendar integration',

            // Reports & Analytics
            'view reports',
            'view analytics',
            'export data',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // === SUPER ADMIN (Platform Level) ===
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all()); // All permissions

        // === ORGANIZATION OWNER ===
        $orgOwner = Role::firstOrCreate(['name' => 'org-owner']);
        $orgOwner->givePermissionTo([
            // Full organization access
            'edit organizations',
            'manage organization settings',
            'view organization activity',

            // User management
            'view users',
            'invite users',
            'edit users',
            'delete users',
            'assign roles',

            // Event management
            'view events',
            'create events',
            'edit events',
            'delete events',
            'publish events',

            // Booking management
            'view all bookings',
            'view own bookings',
            'create bookings',
            'edit bookings',
            'cancel bookings',
            'reschedule bookings',

            // Payment management
            'view payments',
            'process refunds',
            'view payment reports',

            // Promo codes
            'view promo codes',
            'create promo codes',
            'edit promo codes',
            'delete promo codes',

            // Settings
            'view settings',
            'edit settings',

            // Calendar
            'manage calendar integration',

            // Reports
            'view reports',
            'view analytics',
            'export data',
        ]);

        // === ORGANIZATION ADMIN ===
        $orgAdmin = Role::firstOrCreate(['name' => 'org-admin']);
        $orgAdmin->givePermissionTo([
            // Limited organization access
            'view organization activity',

            // User management (limited)
            'view users',
            'invite users',

            // Event management
            'view events',
            'create events',
            'edit events',
            'delete events',
            'publish events',

            // Booking management
            'view all bookings',
            'view own bookings',
            'create bookings',
            'edit bookings',
            'cancel bookings',
            'reschedule bookings',

            // Payment management (view only)
            'view payments',
            'view payment reports',

            // Promo codes
            'view promo codes',
            'create promo codes',
            'edit promo codes',

            // Settings (view only)
            'view settings',

            // Calendar
            'manage calendar integration',

            // Reports
            'view reports',
            'view analytics',
        ]);

        // === ORGANIZATION MEMBER ===
        $orgMember = Role::firstOrCreate(['name' => 'org-member']);
        $orgMember->givePermissionTo([
            // Event management (own events)
            'view events',
            'create events',
            'edit events',

            // Booking management
            'view all bookings',
            'view own bookings',
            'create bookings',
            'edit bookings',
            'cancel bookings',
            'reschedule bookings',

            // Payment management (view only)
            'view payments',

            // Promo codes (view only)
            'view promo codes',

            // Calendar
            'manage calendar integration',
        ]);

        // === GUEST (External Users) ===
        $guest = Role::firstOrCreate(['name' => 'guest']);
        $guest->givePermissionTo([
            // Minimal permissions for external users
            'view events',
            'create bookings',
            'view own bookings',
            'cancel bookings',
        ]);

        $this->command->info('✅ Roles and permissions seeded successfully!');
        $this->command->info('   - super-admin: Platform administrator');
        $this->command->info('   - org-owner: Organization owner');
        $this->command->info('   - org-admin: Organization administrator');
        $this->command->info('   - org-member: Organization team member');
        $this->command->info('   - guest: External user');
    }
}

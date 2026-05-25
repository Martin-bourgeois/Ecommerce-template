<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing permissions and roles
        Permission::query()->delete();
        Role::query()->delete();

        // Define all permissions
        $permissions = [
            // Products
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Orders
            'orders.view',
            'orders.update_status',

            // Customers
            'customers.view',

            // Promotions
            'promotions.manage',

            // Support
            'support.manage',

            // RMA
            'rma.manage',

            // Settings
            'settings.manage',

            // Reports
            'reports.view',
        ];

        // Create permissions
        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[$permission] = Permission::create(['name' => $permission]);
        }

        // Define roles with their permissions
        $rolePermissions = [
            'customer' => [
                'products.view',
                'orders.view',
            ],
            'staff' => [
                'products.view',
                'products.create',
                'products.edit',
                'products.delete',
                'orders.view',
                'orders.update_status',
                'customers.view',
                'promotions.manage',
                'support.manage',
                'rma.manage',
            ],
            'admin' => array_keys($createdPermissions), // All permissions
        ];

        // Create roles and assign permissions
        foreach ($rolePermissions as $roleName => $rolePerms) {
            $role = Role::create(['name' => $roleName]);

            foreach ($rolePerms as $permission) {
                $role->givePermissionTo($createdPermissions[$permission]);
            }
        }

        $this->command->info('✓ Roles and permissions seeded successfully!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'admin',
            'dispatch',
            'partner',
            'business',
            'customer',
        ] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }
}

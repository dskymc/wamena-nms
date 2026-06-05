<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'locations.view',
            'locations.create',
            'locations.update',
            'locations.delete',
            'snmp_profiles.view',
            'snmp_profiles.create',
            'snmp_profiles.update',
            'snmp_profiles.delete',
            'devices.view',
            'devices.create',
            'devices.update',
            'devices.delete',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'locations.view', 'locations.create', 'locations.update', 'locations.delete',
            'snmp_profiles.view', 'snmp_profiles.create', 'snmp_profiles.update', 'snmp_profiles.delete',
            'devices.view', 'devices.create', 'devices.update', 'devices.delete',
            'users.view',
        ]);

        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->syncPermissions([
            'locations.view',
            'snmp_profiles.view', 'snmp_profiles.create', 'snmp_profiles.update', 'snmp_profiles.delete',
            'devices.view', 'devices.create', 'devices.update', 'devices.delete',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'locations.view',
            'snmp_profiles.view',
            'devices.view',
        ]);
    }
}

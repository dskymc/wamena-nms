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
            'topology.view',
            'topology.discover',
            'alert_rules.view',
            'alert_rules.create',
            'alert_rules.update',
            'alert_rules.delete',
            'alert_events.view',
            'snmp_traps.view',
            'notification_settings.update',
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
            'topology.view', 'topology.discover',
            'alert_rules.view', 'alert_rules.create', 'alert_rules.update', 'alert_rules.delete',
            'alert_events.view', 'notification_settings.update',
            'snmp_traps.view',
            'users.view',
        ]);

        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->syncPermissions([
            'locations.view',
            'snmp_profiles.view', 'snmp_profiles.create', 'snmp_profiles.update', 'snmp_profiles.delete',
            'devices.view', 'devices.create', 'devices.update', 'devices.delete',
            'topology.view', 'topology.discover',
            'alert_rules.view', 'alert_rules.create', 'alert_rules.update', 'alert_rules.delete',
            'alert_events.view',
            'snmp_traps.view',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'locations.view',
            'snmp_profiles.view',
            'devices.view',
            'topology.view',
            'alert_rules.view',
            'alert_events.view',
            'snmp_traps.view',
        ]);
    }
}

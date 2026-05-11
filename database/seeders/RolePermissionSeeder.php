<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $attractionPerms = [
            'view_attraction',
            'view_any_attraction',
            'create_attraction',
            'update_attraction',
            'delete_attraction',
            'delete_any_attraction',
            'restore_attraction',
            'restore_any_attraction',
            'replicate_attraction',
            'reorder_attraction',
            'force_delete_attraction',
            'force_delete_any_attraction',
        ];

        $sessionPerms = [
            'view_activity::session',
            'view_any_activity::session',
            'create_activity::session',
            'update_activity::session',
            'delete_activity::session',
            'delete_any_activity::session',
            'restore_activity::session',
            'restore_any_activity::session',
            'replicate_activity::session',
            'reorder_activity::session',
            'force_delete_activity::session',
            'force_delete_any_activity::session',
        ];

        $allocationPerms = [
            'view_guest::allocation',
            'view_any_guest::allocation',
            'create_guest::allocation',
            'update_guest::allocation',
            'delete_guest::allocation',
            'delete_any_guest::allocation',
            'restore_guest::allocation',
            'restore_any_guest::allocation',
            'replicate_guest::allocation',
            'reorder_guest::allocation',
            'force_delete_guest::allocation',
            'force_delete_any_guest::allocation',
        ];

        $widgetPerms = [
            'widget_StatsOverviewWidget',
            'widget_OccupancyChart',
            'widget_AllocationSourceChart',
        ];

        $userPerms = [
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',
            'restore_user',
            'restore_any_user',
            'replicate_user',
            'reorder_user',
            'force_delete_user',
            'force_delete_any_user',
        ];

        $allPerms = array_merge($attractionPerms, $sessionPerms, $allocationPerms, $userPerms, $widgetPerms);

        foreach ($allPerms as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->givePermissionTo([
            'view_any_attraction',
            'view_attraction',
            'view_any_activity::session',
            'view_activity::session',
            'view_any_guest::allocation',
            'view_guest::allocation',
            'create_guest::allocation',
            'update_guest::allocation',
            'delete_guest::allocation',
            'widget_StatsOverviewWidget',
            'widget_OccupancyChart',
            'widget_AllocationSourceChart',
        ]);

        User::where('role', 'admin')->each(fn ($user) => $user->assignRole('admin'));
        User::where('role', 'operator')->each(fn ($user) => $user->assignRole('operator'));
    }
}

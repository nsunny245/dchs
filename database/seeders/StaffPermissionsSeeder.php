<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StaffPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_staff',
            'create_staff',
            'edit_staff',
            'delete_staff',
            'view_staff_salary',
            'manage_staff_salary',
            'submit_leave',
            'approve_leave',
            'submit_attendance_correction',
            'approve_attendance_correction',
            'manage_agreements',
        ];

        foreach (['web', 'campus'] as $guard) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
            }

            $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard]);
            $superAdmin->givePermissionTo(Permission::where('guard_name', $guard)->get());
        }

        $assign = function (string $roleName, array $permissionNames) {
            foreach (['web', 'campus'] as $guard) {
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
                $role->givePermissionTo(Permission::query()
                    ->where('guard_name', $guard)
                    ->whereIn('name', $permissionNames)
                    ->get());
            }
        };

        $assign('Campus Principal', [
            'view_staff',
            'submit_leave',
            'submit_attendance_correction',
        ]);

        $assign('Finance', [
            'view_staff',
            'submit_leave',
        ]);

        $assign('Receptionist', [
            'view_staff',
            'submit_leave',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles/permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            'view any user', 'create user', 'update user', 'delete user',
            'view any campus', 'create campus', 'update campus', 'delete campus',
            'view any course', 'create course', 'update course', 'delete course',
            'view any admission', 'create admission', 'update admission', 'delete admission',
            'view any student', 'create student', 'update student', 'delete student',
            'view any fee', 'create fee', 'update fee', 'delete fee',
            'view any timetable', 'create timetable', 'update timetable', 'delete timetable',
            'view any exam', 'create exam', 'update exam', 'delete exam',
            'view any result', 'create result', 'update result', 'delete result',
            'view any report', 'view dashboard', 'manage settings',
            'view own profile', 'view own fee', 'view own attendance',
            'view own result', 'view own timetable',
            'manage master fee structures', 'request concession', 'approve concession',
            'finalize admission', 'view admission audit', 'record fee payment',
        ];

        foreach (['web', 'campus'] as $guard) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
            }
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

        // 1. Super Admin / Group Admin
        foreach (['web', 'campus'] as $guard) {
            Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard])
                ->givePermissionTo(Permission::where('guard_name', $guard)->get());
        }

        // 2. Campus Principal
        $assign('Campus Principal', [
            'view any user', 'create user', 'update user',
            'view any campus', 'view any course',
            'view any admission', 'create admission', 'update admission',
            'view any student', 'create student', 'update student', 'delete student',
            'view any fee', 'create fee', 'update fee',
            'view any timetable', 'create timetable',
            'view any exam', 'create exam',
            'view any result', 'create result',
            'view any report', 'view dashboard', 'request concession', 'finalize admission',
        ]);

        // 3. Academic Head / Faculty
        $assign('Faculty', [
            'view any student', 'view any timetable',
            'view any exam', 'create result', 'update result',
            'view dashboard',
        ]);

        // 4. Admission Officer
        $assign('Admission Officer', [
            'view any admission', 'create admission', 'update admission', 'delete admission',
            'view any student', 'create student',
            'view dashboard', 'request concession', 'finalize admission',
        ]);

        // 5. Accounts / Finance
        $assign('Finance', [
            'view any student', 'view any fee', 'create fee', 'update fee', 'delete fee',
            'view any report', 'view dashboard', 'record fee payment',
        ]);

        // 6. Student (Frontend + Portal)
        $assign('Student', [
            'view own profile', 'view own fee', 'view own attendance',
            'view own result', 'view own timetable',
        ]);

        // Clear cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}

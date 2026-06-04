<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
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
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 1. Super Admin / Group Admin
        Role::firstOrCreate(['name' => 'Super Admin'])->givePermissionTo(Permission::all());

        // 2. Campus Principal
        Role::firstOrCreate(['name' => 'Campus Principal'])->givePermissionTo([
            'view any user', 'create user', 'update user',
            'view any campus', 'view any course',
            'view any admission', 'create admission', 'update admission',
            'view any student', 'create student', 'update student', 'delete student',
            'view any fee', 'create fee', 'update fee',
            'view any timetable', 'create timetable',
            'view any exam', 'create exam',
            'view any result', 'create result',
            'view any report', 'view dashboard'
        ]);

        // 3. Academic Head / Faculty
        Role::firstOrCreate(['name' => 'Faculty'])->givePermissionTo([
            'view any student', 'view any timetable',
            'view any exam', 'create result', 'update result',
            'view dashboard'
        ]);

        // 4. Admission Officer
        Role::firstOrCreate(['name' => 'Admission Officer'])->givePermissionTo([
            'view any admission', 'create admission', 'update admission', 'delete admission',
            'view any student', 'create student',
            'view dashboard'
        ]);

        // 5. Accounts / Finance
        Role::firstOrCreate(['name' => 'Finance'])->givePermissionTo([
            'view any student', 'view any fee', 'create fee', 'update fee', 'delete fee',
            'view any report', 'view dashboard'
        ]);

        // 6. Student (Frontend + Portal)
        Role::firstOrCreate(['name' => 'Student'])->givePermissionTo([
            'view own profile', 'view own fee', 'view own attendance',
            'view own result', 'view own timetable'
        ]);

        // Clear cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}

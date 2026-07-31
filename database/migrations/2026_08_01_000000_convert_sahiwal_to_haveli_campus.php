<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Campus record
        $campus = Campus::where('city', 'Sahiwal')
            ->orWhere('name', 'LIKE', '%Sahiwal%')
            ->first();

        if ($campus) {
            $campus->update([
                'city' => 'Haveli Lakha',
                'name' => 'Daniyal College Haveli',
                'address' => 'Depalpur-Vasavewala Road, Haveli Lakha',
                'code' => 'HVL',
                'is_active' => true,
            ]);
        } else {
            $campus = Campus::create([
                'city' => 'Haveli Lakha',
                'name' => 'Daniyal College Haveli',
                'address' => 'Depalpur-Vasavewala Road, Haveli Lakha',
                'phone' => '+923217729533',
                'email' => 'info@daniyalgroupofcolleges.com',
                'code' => 'HVL',
                'is_active' => true,
            ]);
        }

        // 2. Update or Create Campus Admin User
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Campus Principal', 'guard_name' => 'web']);
        $admissionRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admission Officer', 'guard_name' => 'web']);

        $admin = User::where('email', 'sahiwal@dchs.com')->first();
        if ($admin) {
            $admin->update([
                'name' => 'Haveli Campus Admin',
                'email' => 'haveli@dchs.com',
                'password' => Hash::make('haveli123'),
                'campus_id' => $campus->id,
                'status' => true,
            ]);
            $admin->assignRole($adminRole);
        } else {
            $admin = User::updateOrCreate(
                ['email' => 'haveli@dchs.com'],
                [
                    'name' => 'Haveli Campus Admin',
                    'password' => Hash::make('haveli123'),
                    'campus_id' => $campus->id,
                    'status' => true,
                ]
            );
            $admin->assignRole($adminRole);
        }

        // 3. Update or Create Receptionist User
        $receptionist = User::where('email', 'sahiwal.reception@dchs.com')->first();
        if ($receptionist) {
            $receptionist->update([
                'name' => 'Haveli Receptionist / Admission Officer',
                'email' => 'haveli.reception@dchs.com',
                'password' => Hash::make('haveli123'),
                'campus_id' => $campus->id,
                'status' => true,
            ]);
            $receptionist->assignRole($admissionRole);
        } else {
            $receptionist = User::updateOrCreate(
                ['email' => 'haveli.reception@dchs.com'],
                [
                    'name' => 'Haveli Receptionist / Admission Officer',
                    'password' => Hash::make('haveli123'),
                    'campus_id' => $campus->id,
                    'status' => true,
                ]
            );
            $receptionist->assignRole($admissionRole);
        }

        // 4. Update website global settings if present
        DB::table('settings')->where('key', 'website_address')->update([
            'value' => json_encode('Okara · Haveli · Depalpur · Chichawatni (Punjab)'),
        ]);
    }

    public function down(): void
    {
        // No rollback needed for campus renaming
    }
};

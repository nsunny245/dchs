<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Campus;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CampusUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'Campus Principal']);

        $campusUsers = [
            [
                'city' => 'Okara',
                'name' => 'Okara Campus Admin',
                'email' => 'okara@dchs.com',
                'password' => 'okara123',
            ],
            [
                'city' => 'Depalpur',
                'name' => 'Depalpur Campus Admin',
                'email' => 'depalpur@dchs.com',
                'password' => 'depalpur123',
            ],
            [
                'city' => 'Chichawatni',
                'name' => 'Chichawatni Campus Admin',
                'email' => 'chichawatni@dchs.com',
                'password' => 'chichawatni123',
            ],
            [
                'city' => 'Sahiwal',
                'name' => 'Sahiwal Campus Admin',
                'email' => 'sahiwal@dchs.com',
                'password' => 'sahiwal123',
            ],
        ];

        foreach ($campusUsers as $cUser) {
            $campus = Campus::where('city', $cUser['city'])->first();
            if ($campus) {
                $user = User::updateOrCreate(
                    ['email' => $cUser['email']],
                    [
                        'name' => $cUser['name'],
                        'password' => Hash::make($cUser['password']),
                        'campus_id' => $campus->id,
                        'status' => true,
                    ]
                );
                $user->assignRole($role);
            }
        }
    }
}

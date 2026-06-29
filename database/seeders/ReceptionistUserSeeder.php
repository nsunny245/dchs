<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Campus;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class ReceptionistUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admission Officer']);

        $receptionists = [
            [
                'city' => 'Okara',
                'name' => 'Okara Receptionist / Admission Officer',
                'email' => 'okara.reception@dchs.com',
                'password' => 'reception123',
            ],
            [
                'city' => 'Depalpur',
                'name' => 'Depalpur Receptionist / Admission Officer',
                'email' => 'depalpur.reception@dchs.com',
                'password' => 'reception123',
            ],
            [
                'city' => 'Chichawatni',
                'name' => 'Chichawatni Receptionist / Admission Officer',
                'email' => 'chichawatni.reception@dchs.com',
                'password' => 'reception123',
            ],
            [
                'city' => 'Sahiwal',
                'name' => 'Sahiwal Receptionist / Admission Officer',
                'email' => 'sahiwal.reception@dchs.com',
                'password' => 'reception123',
            ],
        ];

        foreach ($receptionists as $rec) {
            $campus = Campus::where('city', $rec['city'])->first();
            if ($campus) {
                $user = User::updateOrCreate(
                    ['email' => $rec['email']],
                    [
                        'name' => $rec['name'],
                        'password' => Hash::make($rec['password']),
                        'campus_id' => $campus->id,
                        'status' => true,
                    ]
                );
                $user->assignRole($role);
            }
        }
    }
}

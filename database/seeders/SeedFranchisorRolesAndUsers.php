<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class SeedFranchisorRolesAndUsers extends Seeder
{
    public function run(): void
    {
        // 1. Create Spatie roles
        $inboundRole = Role::findOrCreate('Franchisor Inbound', 'web');
        $outboundRole = Role::findOrCreate('Franchisor Outbound', 'web');

        // 2. Create Inbound User
        $inboundUser = User::updateOrCreate(
            ['email' => 'franchisor.inbound@dchs.com'],
            [
                'name' => 'Franchisor Inbound',
                'password' => Hash::make('password123'),
                'status' => 1,
            ]
        );
        $inboundUser->assignRole($inboundRole);

        // 3. Create Outbound User
        $outboundUser = User::updateOrCreate(
            ['email' => 'franchisor.outbound@dchs.com'],
            [
                'name' => 'Franchisor Outbound',
                'password' => Hash::make('password123'),
                'status' => 1,
            ]
        );
        $outboundUser->assignRole($outboundRole);

        // 4. Create Settings
        Setting::updateOrCreate(
            ['key' => 'franchisor_inbound_user_id'],
            ['value' => (string) $inboundUser->id]
        );

        Setting::updateOrCreate(
            ['key' => 'franchisor_outbound_user_id'],
            ['value' => (string) $outboundUser->id]
        );
    }
}

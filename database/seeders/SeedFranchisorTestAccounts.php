<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Franchisor;
use Illuminate\Support\Facades\Hash;

class SeedFranchisorTestAccounts extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Spatie Roles exist
        $inboundRole = Role::findOrCreate('Franchisor Inbound', 'web');
        $outboundRole = Role::findOrCreate('Franchisor Outbound', 'web');

        // 2. Setup Inbound Franchisor and User
        // Find existing inbound franchisor or create a default one
        $inboundFranchise = Franchisor::where('type', 'inbound')->first();
        if (!$inboundFranchise) {
            $inboundFranchise = Franchisor::create([
                'name' => 'Daniyal Inbound Institute',
                'code' => 'DIB01',
                'type' => 'inbound',
                'contact_person' => 'Inbound Manager',
                'phone' => '03001234567',
                'email' => 'inbound.inst@dchs.com',
                'address' => 'Okara',
                'is_active' => true,
            ]);
        }

        $inboundUser = User::updateOrCreate(
            ['email' => 'test.inbound@dchs.com'],
            [
                'name' => 'Inbound Partner User',
                'password' => Hash::make('password123'),
                'status' => 1,
            ]
        );
        $inboundUser->assignRole($inboundRole);

        // Link the user to the inbound franchisor
        $inboundFranchise->update(['user_id' => $inboundUser->id]);

        // 3. Setup Outbound Franchisor and User
        $outboundFranchise = Franchisor::where('type', 'outbound')->first();
        if (!$outboundFranchise) {
            $outboundFranchise = Franchisor::create([
                'name' => 'Daniyal Outbound Academy',
                'code' => 'DOB01',
                'type' => 'outbound',
                'contact_person' => 'Outbound Coordinator',
                'phone' => '03007654321',
                'email' => 'outbound.acad@dchs.com',
                'address' => 'Lahore',
                'is_active' => true,
            ]);
        }

        $outboundUser = User::updateOrCreate(
            ['email' => 'test.outbound@dchs.com'],
            [
                'name' => 'Outbound Partner User',
                'password' => Hash::make('password123'),
                'status' => 1,
            ]
        );
        $outboundUser->assignRole($outboundRole);

        // Link the user to the outbound franchisor
        $outboundFranchise->update(['user_id' => $outboundUser->id]);
    }
}

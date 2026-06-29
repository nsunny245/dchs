<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicSession;
use App\Models\Campus;

class AcademicSessionSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = [
            ['name' => 'Session 2025', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'is_active' => false],
            ['name' => 'Session 2026-2028', 'start_date' => '2026-01-01', 'end_date' => '2028-12-31', 'is_active' => true],
            ['name' => 'Session 35', 'start_date' => '2026-06-01', 'end_date' => '2027-05-31', 'is_active' => true],
        ];

        foreach ($sessions as $s) {
            AcademicSession::firstOrCreate(['name' => $s['name']], $s);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admission;
use Carbon\Carbon;

class LhvStudentsSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/lhv_students_2025.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("JSON file not found at " . $jsonPath);
            return;
        }

        $students = json_decode(file_get_contents($jsonPath), true);
        $this->command->info("Loaded " . count($students) . " students.");

        $count = 0;
        foreach ($students as $row) {
            // Field mapping:
            // 0: S.No
            // 1: Full Name
            // 2: Father Name
            // 3: CNIC No.
            // 4: Date of Birth
            // 5: Domicile
            // 6: Marks in Matric/Total
            // 7: Residential Address

            if (count($row) < 8) {
                continue;
            }

            $cnic = preg_replace('/[^0-9]/', '', $row[3]); // Clean CNIC
            if (empty($cnic)) {
                continue;
            }
            
            // Format DOB
            $dobStr = str_replace(' ', '', $row[4]);
            try {
                $dob = Carbon::createFromFormat('d-m-Y', $dobStr)->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $dob = Carbon::createFromFormat('Y-m-d', $dobStr)->format('Y-m-d');
                } catch (\Exception $ex) {
                    $dob = '2005-01-01'; // safe fallback
                }
            }

            // Parse Matric obtained/total marks
            $matricObtained = null;
            $matricTotal = null;
            $marksStr = str_replace(' ', '', $row[6]);
            if (str_contains($marksStr, '/')) {
                $parts = explode('/', $marksStr);
                $matricObtained = (int) $parts[0];
                $matricTotal = (int) $parts[1];
            } else {
                $matricObtained = (int) $marksStr;
                $matricTotal = 1100;
            }

            // Insert into admissions
            Admission::updateOrCreate(
                ['cnic' => $cnic],
                [
                    'applicant_name' => $row[1],
                    'father_name' => $row[2],
                    'dob' => $dob,
                    'gender' => 'female',
                    'phone' => '00000000000',
                    'address' => $row[7],
                    'course_id' => 1, // Lady Health Visitor
                    'campus_id' => 1, // Daniyal College Okara
                    'academic_session_id' => 1, // Session 2025
                    'status' => 'approved',
                    'domicile_district' => $row[5],
                    'matric_obtained_marks' => $matricObtained,
                    'matric_total_marks' => $matricTotal,
                    'cnic_copy' => null,
                    'matric_copy' => null,
                    'domicile_copy' => null,
                ]
            );
            $count++;
        }
        $this->command->info("Successfully seeded {$count} LHV student records.");
    }
}

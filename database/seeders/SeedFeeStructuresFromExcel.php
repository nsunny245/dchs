<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeStructure;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SeedFeeStructuresFromExcel extends Seeder
{
    public function run(): void
    {
        $filePath = public_path('templates/fee_structure_template.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("Excel template file not found at: {$filePath}");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        // We will read from the first sheet (or the one named 'Daniyal College Okara')
        $sheet = $spreadsheet->getSheet(0);

        if (!$sheet) {
            $this->command->error("No sheets found in the Excel template.");
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $campuses = Campus::all();

        for ($row = 6; $row <= $highestRow; $row++) {
            $courseCode = $sheet->getCell('A' . $row)->getValue();
            if (empty($courseCode)) {
                continue;
            }

            $course = Course::where('code', $courseCode)->first();
            if (!$course) {
                $this->command->warn("Course with code '{$courseCode}' not found in database. Skipping row {$row}.");
                continue;
            }

            // Extract data values
            $totalFee = (float) $sheet->getCell('D' . $row)->getValue();
            $installmentsCount = (int) $sheet->getCell('E' . $row)->getValue();
            $hostelDues = (float) $sheet->getCell('F' . $row)->getValue();
            $lateFee = (float) $sheet->getCell('G' . $row)->getValue();
            $admissionFee = (float) $sheet->getCell('H' . $row)->getValue();
            $verificationFee = (float) $sheet->getCell('I' . $row)->getValue();
            $enrollmentFee = (float) $sheet->getCell('J' . $row)->getValue();
            $examinationFee = (float) $sheet->getCell('K' . $row)->getValue();
            $otherMisc = (float) $sheet->getCell('L' . $row)->getValue();

            // Seed/Update for all campuses
            foreach ($campuses as $campus) {
                FeeStructure::updateOrCreate(
                    [
                        'campus_id' => $campus->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'total_fee' => $totalFee,
                        'installment_count' => $installmentsCount,
                        'late_fee' => $lateFee,
                        'admission_fee' => $admissionFee,
                        'hostel_dues' => $hostelDues,
                        'verification_fee' => $verificationFee,
                        'enrollment_fee' => $enrollmentFee,
                        'examination_fee' => $examinationFee,
                        'other_misc' => $otherMisc,
                    ]
                );
            }
        }

        $this->command->info("Fee structures successfully imported from Excel for all campuses!");
    }
}

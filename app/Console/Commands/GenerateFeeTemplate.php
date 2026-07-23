<?php

namespace App\Console\Commands;

use App\Models\Campus;
use App\Models\Course;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateFeeTemplate extends Command
{
    protected $signature = 'fee:generate-template';

    protected $description = 'Generate a styled multi-tab Excel template for Daniyal Group of Colleges fee structures';

    public function handle(): void
    {
        $campuses = Campus::all();
        $courses = Course::all();

        if ($campuses->isEmpty() || $courses->isEmpty()) {
            $this->error('Please ensure campuses and courses are seeded first.');

            return;
        }

        $spreadsheet = new Spreadsheet;
        // Remove the default sheet
        $spreadsheet->removeSheetByIndex(0);

        foreach ($campuses as $campusIndex => $campus) {
            // Sheet title cannot exceed 31 chars
            $sheetTitle = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $campus->name), 0, 30);
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($sheetTitle);

            // Set gridlines visible
            $sheet->setShowGridlines(true);

            // 1. Write Header/Instructions
            $sheet->setCellValue('A1', 'DANIYAL GROUP OF COLLEGES - FEE STRUCTURE CONFIGURATION');
            $sheet->setCellValue('A2', "Campus: {$campus->name} (Campus ID: {$campus->id})");
            $sheet->setCellValue('A3', 'INSTRUCTIONS: Fill in fee amounts for each program. Do NOT alter Course Codes or Course Names.');

            // Styles for Title block
            $sheet->mergeCells('A1:J1');
            $sheet->mergeCells('A2:J2');
            $sheet->mergeCells('A3:J3');

            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new Color('FFFFFF'));
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0A1526'); // Dark Navy

            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->setColor(new Color('0A1526'));
            $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->setColor(new Color('556B82'));

            // 2. Table Column Headers (Row 5)
            $headers = [
                'Course Code',
                'Course Name',
                'Academic Year / Session',
                'Total Fee (PKR)',
                'Installment Count',
                'Late Fee Per Day (PKR)',
                'Admission Fee (PKR)',
                'Verification Fee (PKR)',
                'Enrollment Fee (PKR)',
                'Other Miscellaneous (PKR)',
            ];

            foreach ($headers as $colIndex => $header) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($colLetter.'5', $header);
            }

            // Style headers
            $headerRange = 'A5:J5';
            $headerStyle = $sheet->getStyle($headerRange);
            $headerStyle->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
            $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1A2E4F'); // Secondary Navy
            $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $headerStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('C5A85A'); // Golden border

            // 3. Populate Courses
            $row = 6;
            foreach ($courses as $course) {
                $sheet->setCellValue('A'.$row, $course->code);
                $sheet->setCellValue('B'.$row, $course->name);
                $sheet->setCellValue('C'.$row, '2026-2028');
                $sheet->setCellValue('D'.$row, 0.00);
                $sheet->setCellValue('E'.$row, 12); // Default to monthly installments over 1-2 years
                $sheet->setCellValue('F'.$row, 100.00);
                $sheet->setCellValue('G'.$row, 0.00);
                $sheet->setCellValue('H'.$row, 0.00);
                $sheet->setCellValue('I'.$row, 0.00);
                $sheet->setCellValue('J'.$row, 0.00);

                // Row borders and alignments
                $rowRange = "A{$row}:J{$row}";
                $rowStyle = $sheet->getStyle($rowRange);
                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('E2E8F0');

                // Alignments & formats
                $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Number formatting for fee columns (D, F, G, H, I, J)
                foreach (['D', 'F', 'G', 'H', 'I', 'J'] as $col) {
                    $sheet->getStyle($col.$row)->getNumberFormat()->setFormatCode('#,##0.00');
                }

                $row++;
            }

            // Auto-fit column widths
            foreach (range(1, 10) as $colIndex) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }
        }

        // Save Spreadsheet
        $directory = public_path('templates');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory.'/fee_structure_template.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $this->info("Excel Fee Structure Template successfully generated at: {$filePath}");
    }
}

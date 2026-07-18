<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\FeeStructure;
use App\Models\FeePayment;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function afterCreate(): void
    {
        $student = $this->record;
        $admission = $student->admission;

        $hasCustom = false;

        // Case 1: If custom installments were configured during admission, use them exactly
        if ($admission && $admission->custom_installments) {
            $installments = is_string($admission->custom_installments) 
                ? json_decode($admission->custom_installments, true) 
                : $admission->custom_installments;

            if (is_array($installments) && count($installments) > 0) {
                $hasCustom = true;

                // Find or create a FeeStructure for this course/campus to satisfy database FK constraints
                $feeStructure = FeeStructure::firstOrCreate(
                    [
                        'campus_id' => $student->campus_id,
                        'course_id' => $student->course_id,
                    ],
                    [
                        'total_fee' => collect($installments)->sum('amount'),
                        'installment_count' => count($installments),
                        'late_fee' => 50.00,
                    ]
                );

                foreach ($installments as $index => $inst) {
                    FeePayment::create([
                        'campus_id' => $student->campus_id,
                        'student_id' => $student->id,
                        'fee_structure_id' => $feeStructure->id,
                        'installment_no' => $index + 1,
                        'amount' => $inst['amount'],
                        'due_date' => $inst['due_date'] ?? now()->addMonth(),
                        'status' => 'unpaid',
                    ]);
                }
            }
        }

        // Case 2: Fallback to standard FeeStructure if no custom plan was defined
        if (!$hasCustom) {
            $feeStructure = FeeStructure::where('campus_id', $student->campus_id)
                ->where('course_id', $student->course_id)
                ->first();

            if ($feeStructure) {
                $instNo = 1;

                // 1. Admission and Registration Dues (Combined initial fees)
                $initialDues = (float)$feeStructure->admission_fee 
                    + (float)$feeStructure->verification_fee 
                    + (float)$feeStructure->enrollment_fee 
                    + (float)$feeStructure->other_misc;

                if ($initialDues > 0) {
                    FeePayment::create([
                        'campus_id' => $student->campus_id,
                        'student_id' => $student->id,
                        'fee_structure_id' => $feeStructure->id,
                        'installment_no' => $instNo++,
                        'amount' => $initialDues,
                        'due_date' => now(), // Due immediately on admission
                        'status' => 'unpaid',
                    ]);
                }

                // 2. Examination Fee
                if ((float)$feeStructure->examination_fee > 0) {
                    FeePayment::create([
                        'campus_id' => $student->campus_id,
                        'student_id' => $student->id,
                        'fee_structure_id' => $feeStructure->id,
                        'installment_no' => $instNo++,
                        'amount' => $feeStructure->examination_fee,
                        'due_date' => now()->addMonths(5), // Due after 5 months typically
                        'status' => 'unpaid',
                    ]);
                }

                // 3. Tuition Fee installments split equally
                $totalTuition = (float)$feeStructure->total_fee;
                $installmentsCount = $feeStructure->installment_count ?: 12;

                if ($totalTuition > 0) {
                    $installmentAmount = round($totalTuition / $installmentsCount, 2);
                    for ($i = 1; $i <= $installmentsCount; $i++) {
                        FeePayment::create([
                            'campus_id' => $student->campus_id,
                            'student_id' => $student->id,
                            'fee_structure_id' => $feeStructure->id,
                            'installment_no' => $instNo++,
                            'amount' => $installmentAmount,
                            'due_date' => now()->addMonths($i), // Billed monthly
                            'status' => 'unpaid',
                        ]);
                    }
                }
            }
        }
    }
}

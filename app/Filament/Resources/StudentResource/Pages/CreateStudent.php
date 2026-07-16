<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function afterCreate(): void
    {
        $student = $this->record;
        $admission = $student->admission;

        if ($admission && $admission->custom_installments) {
            $installments = is_string($admission->custom_installments) 
                ? json_decode($admission->custom_installments, true) 
                : $admission->custom_installments;

            if (is_array($installments) && count($installments) > 0) {
                // Find or create a FeeStructure for this course/campus to satisfy database FK constraints
                $feeStructure = \App\Models\FeeStructure::firstOrCreate(
                    [
                        'campus_id' => $student->campus_id,
                        'course_id' => $student->course_id,
                    ],
                    [
                        'academic_year' => $admission->academicSession?->name ?? date('Y'),
                        'total_fee' => collect($installments)->sum('amount'),
                        'installment_count' => count($installments),
                        'late_fee' => 100.00,
                    ]
                );

                foreach ($installments as $index => $inst) {
                    \App\Models\FeePayment::create([
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
    }
}

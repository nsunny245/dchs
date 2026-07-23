<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\StudentFeeAccount;
use App\Services\Fees\FeeVoucherService;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function afterCreate(): void
    {
        $student = $this->record;
        
        // Find or create StudentFeeAccount
        $feeAccount = StudentFeeAccount::firstOrCreate(
            ['student_id' => $student->id],
            [
                'admission_id' => $student->admission_id ?? null,
                'original_fee' => 0.00,
                'concession_amount' => 0.00,
                'net_payable' => 0.00,
                'amount_paid' => 0.00,
                'balance' => 0.00,
                'status' => 'active',
            ]
        );

        $feeStructure = FeeStructure::where('course_id', $student->course_id)->first() ?? FeeStructure::first();

        if ($feeStructure) {
            $admission = $student->admission;
            if (!$admission) {
                $admission = Admission::create([
                    'applicant_name' => $student->full_name,
                    'father_name' => 'N/A',
                    'dob' => now()->subYears(18)->toDateString(),
                    'gender' => 'male',
                    'cnic' => 'N/A-' . rand(100000, 999999),
                    'phone' => 'N/A',
                    'address' => 'N/A',
                    'course_id' => $student->course_id,
                    'campus_id' => $student->campus_id,
                    'status' => 'approved',
                ]);

                $student->update(['admission_id' => $admission->id]);
                $feeAccount->update(['admission_id' => $admission->id]);
            }

            // Generate enrollment voucher
            $firstVoucher = FeeVoucherService::generateEnrollmentVoucher($student, $admission, $feeStructure);
            
            // Generate remaining installment vouchers
            $installmentCount = $feeStructure->installment_count ?: 12;
            $monthlyTuition = $feeStructure->total_fee / $installmentCount;
            for ($i = 2; $i <= $installmentCount; $i++) {
                try {
                    FeeVoucherService::generateInstallmentVoucher($student, $i, $monthlyTuition);
                } catch (\Exception $e) {
                    // Prevent crash if installment voucher exists
                }
            }

            // Recalculate account totals
            FeeVoucherService::recalculateAccountTotals($feeAccount);
        }
    }
}

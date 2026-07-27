<?php

namespace App\Filament\Resources\FeeVoucherResource\Pages;

use App\Filament\Resources\FeeVoucherResource;
use App\Models\Campus;
use App\Services\Fees\FeeVoucherService;
use Filament\Resources\Pages\CreateRecord;

class CreateFeeVoucher extends CreateRecord
{
    protected static string $resource = FeeVoucherResource::class;

    public function mount(): void
    {
        parent::mount();

        $studentId = request()->query('student_id');
        if ($studentId) {
            $student = \App\Models\Student::find($studentId);
            if ($student) {
                $this->form->fill([
                    'student_id' => $student->id,
                    'campus_id' => $student->campus_id,
                    'course_id' => $student->course_id,
                    'admission_id' => $student->admission_id,
                    'academic_session_id' => $student->admission?->academic_session_id,
                    'voucher_type' => 'monthly_installment',
                    'orientation' => 'portrait_three_part',
                ]);
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $campus = Campus::find($data['campus_id']);
        $year = now()->format('Y');
        
        $voucherData = FeeVoucherService::generateVoucherNumber($campus, $data['voucher_type'], $year);
        
        $data['voucher_number'] = $voucherData['number'];
        $data['sequence_no'] = $voucherData['sequence'];
        $data['generated_by'] = filament()->auth()->id() ?? 1;

        return $data;
    }
}

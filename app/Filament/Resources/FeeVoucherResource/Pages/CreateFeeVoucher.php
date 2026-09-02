<?php

namespace App\Filament\Resources\FeeVoucherResource\Pages;

use App\Filament\Resources\FeeVoucherResource;
use App\Models\Campus;
use App\Models\FeeVoucherAudit;
use App\Models\Student;
use App\Services\Fees\FeeVoucherCalculator;
use App\Services\Fees\FeeVoucherService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateFeeVoucher extends CreateRecord
{
    protected static string $resource = FeeVoucherResource::class;

    public function mount(): void
    {
        parent::mount();

        $studentId = request()->query('student_id');
        if ($studentId) {
            $student = Student::with('feeAccount')->find($studentId);
            if ($student) {
                $this->form->fill([
                    'student_id' => $student->id,
                    'campus_id' => $student->campus_id,
                    'course_id' => $student->course_id,
                    'admission_id' => $student->admission_id,
                    'academic_session_id' => $student->admission?->academic_session_id,
                    'student_fee_account_id' => $student->feeAccount?->id,
                    'title' => 'Custom Fee Voucher',
                    'voucher_type' => 'monthly_installment',
                    'orientation' => 'portrait_three_part',
                ]);
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $student = Student::with('feeAccount')->find($data['student_id']);

        if (! $student?->feeAccount) {
            throw ValidationException::withMessages([
                'data.student_id' => 'This student does not have an active fee account. Complete the admission enrollment before creating a voucher.',
            ]);
        }

        $campus = Campus::find($data['campus_id']);

        if (! $campus) {
            throw ValidationException::withMessages([
                'data.student_id' => 'The selected student does not have a valid campus.',
            ]);
        }

        $year = now()->format('Y');

        $voucherData = FeeVoucherService::generateVoucherNumber($campus, $data['voucher_type'], $year);

        $data['voucher_number'] = $voucherData['number'];
        $data['sequence_no'] = $voucherData['sequence'];
        $data['generated_by'] = filament()->auth()->id() ?? 1;
        $data['student_fee_account_id'] = $student->feeAccount->id;
        $data['title'] = trim((string) ($data['title'] ?? '')) ?: 'Custom Fee Voucher';
        $data['status'] = 'draft';
        $data['paid_amount'] = 0;
        $data['subtotal'] = 0;
        $data['total_amount'] = 0;
        $data['balance_amount'] = 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        $voucher = $this->record->load('items.feeHead');
        $voucher->update(FeeVoucherCalculator::calculate($voucher));
        FeeVoucherService::recalculateAccountTotals($voucher->feeAccount);

        FeeVoucherAudit::create([
            'fee_voucher_id' => $voucher->id,
            'user_id' => filament()->auth()->id(),
            'action' => 'created',
            'new_values' => $voucher->fresh()->toArray(),
            'ip_address' => request()->ip(),
            'notes' => 'Custom voucher created from the fee voucher form.',
        ]);
    }
}

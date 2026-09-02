<?php

namespace App\Services\Fees;

use App\Models\FeeHead;
use Illuminate\Validation\ValidationException;

class OfficialFeePlanData
{
    public function forAdmission(array $data): array
    {
        $structure = app(OfficialFeeStructureResolver::class)->resolve(
            (int) ($data['course_id'] ?? 0),
            isset($data['campus_id']) ? (int) $data['campus_id'] : null,
            isset($data['academic_session_id']) ? (int) $data['academic_session_id'] : null,
            $data['admission_date'] ?? null,
        );

        if (! $structure) {
            throw ValidationException::withMessages([
                'data.course_id' => 'No active official fee plan exists for this campus, session, course, and date.',
            ]);
        }

        $heads = FeeHead::query()->where('course_id', $data['course_id'])->get();
        $amount = fn (callable $filter) => (string) ($heads->first($filter)?->default_amount ?? '0.00');
        $money = app(InstallmentPlanGenerator::class);
        $otherPaisa = $heads
            ->whereIn('category', ['affiliation', 'miscellaneous', 'hostel'])
            ->sum(fn ($head) => $money->toPaisa($head->default_amount));

        return [
            'custom_tuition_fee' => $structure->total_fee,
            'custom_installment_count' => $structure->installment_count,
            // Admission charges are included in the program tuition package.
            'custom_admission_fee' => '0.00',
            'custom_enrollment_fee' => '0.00',
            'custom_verification_fee' => $amount(fn ($head) => str_starts_with($head->code, 'VERIFICATION_')),
            'custom_examination_fee' => $amount(fn ($head) => str_starts_with($head->code, 'EXAM_')),
            'custom_other_misc' => number_format($otherPaisa / 100, 2, '.', ''),
        ];
    }
}

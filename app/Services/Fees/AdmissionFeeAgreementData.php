<?php

namespace App\Services\Fees;

use App\Models\Admission;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AdmissionFeeAgreementData
{
    public function build(Admission $admission): array
    {
        $plan = $admission->custom_installment_count !== null
            ? [
                'custom_tuition_fee' => $admission->custom_tuition_fee,
                'custom_installment_count' => $admission->custom_installment_count,
                'custom_verification_fee' => $admission->custom_verification_fee,
                'custom_examination_fee' => $admission->custom_examination_fee,
                'custom_other_misc' => (float) $admission->custom_other_misc + (float) $admission->custom_enrollment_fee,
            ]
            : $this->officialPlan($admission);

        $tuition = (float) ($plan['custom_tuition_fee'] ?? 0);
        $verification = (float) ($plan['custom_verification_fee'] ?? 0);
        $examination = (float) ($plan['custom_examination_fee'] ?? 0);
        $other = (float) ($plan['custom_other_misc'] ?? 0);
        $concession = (float) $admission->concession_amount;
        $count = max(1, (int) ($plan['custom_installment_count'] ?? 1));
        $schedule = collect($admission->custom_installments ?? [])
            ->filter(fn (array $row): bool => (float) ($row['amount'] ?? 0) > 0)
            ->values()
            ->map(fn (array $row, int $index): array => [
                'title' => filled($row['title'] ?? null) ? $row['title'] : 'Installment #'.($index + 1),
                'due_date' => Carbon::parse($row['due_date'] ?? $admission->admission_date ?? now()),
                'amount' => (float) $row['amount'],
            ]);

        if ($schedule->isEmpty()) {
            $schedule = collect(app(InstallmentPlanGenerator::class)->generate(
                $tuition,
                $count,
                Carbon::parse($admission->admission_date ?? now()),
            ))->map(fn (array $row): array => [
                'title' => $row['title'],
                'due_date' => Carbon::parse($row['due_date']),
                'amount' => $row['gross_paisa'] / 100,
            ]);
        }

        // Only tuition is contractually payable through the installment plan.
        // The remaining figures are displayed as an informational breakdown.
        $totalPackage = $tuition;

        return [
            'tuition' => $tuition,
            'verification' => $verification,
            'examination' => $examination,
            'other' => $other,
            'additional_breakdown_total' => $verification + $examination + $other,
            'concession' => $concession,
            'total_package' => $totalPackage,
            'net_payable' => max(0, $totalPackage - $concession),
            'schedule' => $schedule,
        ];
    }

    protected function officialPlan(Admission $admission): array
    {
        try {
            return app(OfficialFeePlanData::class)->forAdmission([
                'course_id' => $admission->course_id,
                'campus_id' => $admission->campus_id,
                'academic_session_id' => $admission->academic_session_id,
                'admission_date' => $admission->admission_date,
            ]);
        } catch (ValidationException) {
            return [];
        }
    }
}

<?php

namespace App\Services\Fees;

use App\Models\FeeHead;
use App\Models\FeeVoucher;
use App\Models\FeeVoucherAudit;
use App\Models\FeeVoucherItem;
use App\Models\StudentFeeAccount;
use App\Models\StudentFeeSnapshot;
use App\Models\StudentInstallment;
use App\Models\StudentLedgerEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdmissionVoucherReconciliationService
{
    public function reconcile(StudentFeeAccount $account, ?int $actorId = null): void
    {
        DB::transaction(function () use ($account, $actorId): void {
            $account = StudentFeeAccount::query()->lockForUpdate()->findOrFail($account->id);
            $admission = $account->admission;

            if (! $admission) {
                throw ValidationException::withMessages(['account' => 'This fee account is not linked to an admission.']);
            }

            $schedule = collect($admission->custom_installments ?? [])
                ->filter(fn (array $row): bool => (float) ($row['amount'] ?? 0) > 0)
                ->values();

            if ($schedule->isEmpty()) {
                throw ValidationException::withMessages([
                    'account' => 'No editable admission installment schedule is saved for this student.',
                ]);
            }

            $vouchers = FeeVoucher::query()
                ->where('student_fee_account_id', $account->id)
                ->where('admission_id', $admission->id)
                ->whereNotIn('status', ['cancelled', 'void'])
                ->orderBy('due_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $admissionFee = round(max(0, (float) $admission->custom_admission_fee), 2);
            $tuition = round(max(0, (float) $admission->custom_tuition_fee), 2);
            $concession = min(round(max(0, (float) $admission->concession_amount), 2), $tuition);
            $remainingTuition = max(0, $tuition - $concession - $admissionFee);
            $scheduledTuition = round((float) $schedule->sum(
                fn (array $row): float => (float) ($row['amount'] ?? 0)
            ), 2);

            if (abs($scheduledTuition - $remainingTuition) > 0.01) {
                throw ValidationException::withMessages([
                    'account' => 'The saved tuition installments do not equal the remaining tuition after concession and admission fee. Review the admission fee plan before synchronizing.',
                ]);
            }

            $admissionVoucher = $vouchers->firstWhere('voucher_type', 'new_enrollment');
            $tuitionVouchers = $vouchers->where('voucher_type', 'monthly_installment')->values();
            $legacyAdmissionInstallmentId = $admissionVoucher?->installment_id;

            $paidTuitionVoucher = $tuitionVouchers->first(fn (FeeVoucher $voucher): bool => (float) $voucher->paid_amount > 0
                || $voucher->payments()->where('status', 'paid')->where('amount', '>', 0)->exists()
                || $voucher->allocations()->where('amount', '>', 0)->exists()
            );

            if ($paidTuitionVoucher) {
                throw ValidationException::withMessages([
                    'account' => "{$paidTuitionVoucher->title} already has a payment. Paid tuition vouchers are protected and cannot be rewritten automatically.",
                ]);
            }

            if ($tuitionVouchers->count() !== $schedule->count()) {
                throw ValidationException::withMessages([
                    'account' => 'The saved tuition schedule and active tuition voucher count differ. Review this account manually to preserve its history.',
                ]);
            }

            if ($admissionFee > 0 && ! $admissionVoucher) {
                $number = FeeVoucherService::generateVoucherNumber($account->student->campus, 'new_enrollment', now()->year);
                $admissionVoucher = FeeVoucher::create([
                    'student_id' => $account->student_id,
                    'admission_id' => $admission->id,
                    'title' => 'Admission Fee',
                    'campus_id' => $account->student->campus_id,
                    'course_id' => $account->student->course_id,
                    'academic_session_id' => $admission->academic_session_id,
                    'student_fee_account_id' => $account->id,
                    'voucher_number' => $number['number'],
                    'voucher_type' => 'new_enrollment',
                    'orientation' => 'portrait_three_part',
                    'issue_date' => now(),
                    'due_date' => $admission->admission_date ?: now(),
                    'status' => 'issued',
                    'sequence_no' => $number['sequence'],
                    'subtotal' => $admissionFee,
                    'total_amount' => $admissionFee,
                    'paid_amount' => 0,
                    'balance_amount' => $admissionFee,
                    'generated_by' => $actorId,
                    'metadata' => ['source' => 'admission_form', 'fee_component' => 'admission_fee'],
                ]);
            }

            if ($admissionVoucher) {
                $admissionPaid = max(
                    (float) $admissionVoucher->paid_amount,
                    (float) $admissionVoucher->payments()->where('status', 'paid')->sum('amount'),
                    (float) $admissionVoucher->allocations()->sum('amount'),
                );

                if ($admissionFee + 0.01 < $admissionPaid) {
                    throw ValidationException::withMessages([
                        'account' => 'The saved admission fee is lower than the amount already collected. Increase the admission fee or review this account manually.',
                    ]);
                }

                $admissionHead = FeeHead::firstOrCreate(
                    ['code' => 'ADMISSION'],
                    ['name' => 'Admission Fee', 'category' => 'admission', 'default_amount' => $admissionFee, 'applies_to' => 'new_enrollment', 'is_active' => true],
                );
                $admissionVoucher->update([
                    'title' => 'Admission Fee',
                    'subtotal' => $admissionFee,
                    'discount_amount' => 0,
                    'total_amount' => $admissionFee,
                    'paid_amount' => $admissionPaid,
                    'balance_amount' => max(0, $admissionFee - $admissionPaid),
                    'status' => $admissionPaid >= $admissionFee
                        ? 'paid'
                        : ($admissionPaid > 0 ? 'partially_paid' : 'issued'),
                    'installment_id' => null,
                    'metadata' => array_merge($admissionVoucher->metadata ?? [], ['reconciled_from_admission_plan' => true]),
                ]);
                $admissionVoucher->items()->delete();
                FeeVoucherItem::create([
                    'fee_voucher_id' => $admissionVoucher->id,
                    'fee_head_id' => $admissionHead->id,
                    'description' => 'Admission Fee',
                    'quantity' => 1,
                    'unit_amount' => $admissionFee,
                    'amount' => $admissionFee,
                    'sort_order' => 0,
                ]);
            }

            $tuitionHead = FeeHead::firstOrCreate(
                ['code' => 'TUITION_REC'],
                [
                    'name' => 'Tuition Fee / Installment',
                    'category' => 'tuition',
                    'default_amount' => 0,
                    'applies_to' => 'monthly_installment',
                    'is_active' => true,
                    'sort_order' => 1,
                ],
            );

            // Legacy records linked the admission-fee voucher to installment #1
            // and numbered tuition rows from #2. Move tuition rows temporarily so
            // the unique account/number key can be reassigned safely below.
            $linkedInstallments = $tuitionVouchers->pluck('installment')->filter()->values();
            foreach ($linkedInstallments as $index => $installment) {
                $installment->update(['installment_number' => 1000 + $index]);
            }

            if ($legacyAdmissionInstallmentId) {
                StudentInstallment::query()
                    ->whereKey($legacyAdmissionInstallmentId)
                    ->whereDoesntHave('voucher')
                    ->delete();
            }

            foreach ($schedule as $index => $row) {
                /** @var FeeVoucher $voucher */
                $voucher = $tuitionVouchers->get($index);
                $gross = round((float) $row['amount'], 2);
                $net = $gross;
                $title = trim((string) ($row['title'] ?? '')) ?: 'Tuition Installment #'.($index + 1);
                $dueDate = Carbon::parse($row['due_date'] ?? $admission->admission_date ?? now());

                $before = $voucher->only(['title', 'due_date', 'subtotal', 'discount_amount', 'total_amount']);
                $voucher->update([
                    'title' => $title,
                    'voucher_type' => 'monthly_installment',
                    'due_date' => $dueDate,
                    'status' => $admissionFee <= 0 && $index === 0 ? 'issued' : 'upcoming',
                    'subtotal' => $gross,
                    'discount_amount' => 0,
                    'scholarship_amount' => 0,
                    'previous_balance' => 0,
                    'fine_amount' => 0,
                    'late_fee_amount' => 0,
                    'total_amount' => $net,
                    'paid_amount' => 0,
                    'balance_amount' => $net,
                    'metadata' => array_merge($voucher->metadata ?? [], ['reconciled_from_admission_plan' => true]),
                ]);

                $voucher->items()->delete();
                FeeVoucherItem::create([
                    'fee_voucher_id' => $voucher->id,
                    'fee_head_id' => $tuitionHead->id,
                    'description' => $title,
                    'quantity' => 1,
                    'unit_amount' => $gross,
                    'amount' => $gross,
                    'sort_order' => 1,
                ]);

                if ($voucher->installment_id) {
                    $voucher->installment()->update([
                        'installment_number' => $index + 1,
                        'title' => $title,
                        'due_date' => $dueDate,
                        'gross_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($gross),
                        'concession_paisa' => 0,
                        'net_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($net),
                    ]);
                }

                FeeVoucherAudit::create([
                    'fee_voucher_id' => $voucher->id,
                    'user_id' => $actorId,
                    'action' => 'reconciled',
                    'old_values' => $before,
                    'new_values' => $voucher->fresh()->only(['title', 'due_date', 'subtotal', 'discount_amount', 'total_amount']),
                    'ip_address' => request()->ip(),
                    'notes' => 'Voucher synchronized with the saved admission installment schedule.',
                ]);
            }

            $netPayable = max(0, $tuition - $concession);
            $amountPaid = round((float) $account->payments()->where('status', 'paid')->sum('amount'), 2);
            $account->update([
                'original_fee' => $tuition,
                'concession_amount' => $concession,
                'net_payable' => $netPayable,
                'amount_paid' => $amountPaid,
                'balance' => max(0, $netPayable - $amountPaid),
                'status' => $amountPaid >= $netPayable ? 'paid' : 'active',
            ]);

            StudentFeeSnapshot::where('student_id', $account->student_id)->update([
                'original_package_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($tuition),
                'concession_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($concession),
                'net_payable_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($netPayable),
                'installment_count' => $schedule->count(),
                'installment_schedule' => $schedule->map(fn (array $row, int $index): array => [
                    'number' => $index + 1,
                    'title' => trim((string) ($row['title'] ?? '')) ?: 'Tuition Installment #'.($index + 1),
                    'due_date' => Carbon::parse($row['due_date'])->toDateString(),
                    'gross_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($row['amount']),
                    'concession_paisa' => 0,
                    'net_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($row['amount']),
                ])->all(),
            ]);

            StudentLedgerEntry::where('entry_uuid', "admission-fees-{$admission->id}")
                ->update(['debit_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($tuition)]);
        });
    }
}

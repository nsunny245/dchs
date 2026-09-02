<?php

namespace App\Services\Fees;

use App\Models\FeeHead;
use App\Models\FeeVoucher;
use App\Models\FeeVoucherAudit;
use App\Models\FeeVoucherItem;
use App\Models\StudentFeeAccount;
use App\Models\StudentFeeSnapshot;
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

            if ($account->payments()->exists() || (float) $account->amount_paid > 0) {
                throw ValidationException::withMessages([
                    'account' => 'This account already has payments. Its voucher history cannot be rebuilt automatically.',
                ]);
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

            if ($vouchers->count() !== $schedule->count()) {
                throw ValidationException::withMessages([
                    'account' => 'The saved schedule and active voucher count differ. Review this account manually to preserve its history.',
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

            $tuition = round((float) $schedule->sum(fn (array $row): float => (float) $row['amount']), 2);
            $concession = min(round((float) $admission->concession_amount, 2), $tuition);
            $remainingConcession = $concession;

            foreach ($schedule as $index => $row) {
                /** @var FeeVoucher $voucher */
                $voucher = $vouchers->get($index);
                $gross = round((float) $row['amount'], 2);
                $discount = min($remainingConcession, $gross);
                $net = max(0, $gross - $discount);
                $remainingConcession -= $discount;
                $title = trim((string) ($row['title'] ?? '')) ?: 'Tuition Installment #'.($index + 1);
                $dueDate = Carbon::parse($row['due_date'] ?? $admission->admission_date ?? now());

                $before = $voucher->only(['title', 'due_date', 'subtotal', 'discount_amount', 'total_amount']);
                $voucher->update([
                    'title' => $title,
                    'voucher_type' => 'monthly_installment',
                    'due_date' => $dueDate,
                    'status' => $index === 0 ? 'issued' : 'upcoming',
                    'subtotal' => $gross,
                    'discount_amount' => $discount,
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
                        'title' => $title,
                        'due_date' => $dueDate,
                        'gross_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($gross),
                        'concession_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($discount),
                        'net_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($net),
                    ]);
                }

                FeeVoucherAudit::create([
                    'fee_voucher_id' => $voucher->id,
                    'user_id' => $actorId ?: 1,
                    'action' => 'reconciled',
                    'old_values' => $before,
                    'new_values' => $voucher->fresh()->only(['title', 'due_date', 'subtotal', 'discount_amount', 'total_amount']),
                    'ip_address' => request()->ip(),
                    'notes' => 'Voucher synchronized with the saved admission installment schedule.',
                ]);
            }

            $netPayable = max(0, $tuition - $concession);
            $account->update([
                'original_fee' => $tuition,
                'concession_amount' => $concession,
                'net_payable' => $netPayable,
                'amount_paid' => 0,
                'balance' => $netPayable,
                'status' => $netPayable <= 0 ? 'paid' : 'active',
            ]);

            StudentFeeSnapshot::where('student_id', $account->student_id)->update([
                'original_package_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($tuition),
                'concession_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($concession),
                'net_payable_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($netPayable),
            ]);

            StudentLedgerEntry::where('entry_uuid', "admission-fees-{$admission->id}")
                ->update(['debit_paisa' => app(InstallmentPlanGenerator::class)->toPaisa($tuition)]);
        });
    }
}

<?php

namespace App\Services\Fees;

use App\Models\FeeVoucher;
use App\Models\FeeVoucherAudit;
use App\Models\StudentFeeAccount;
use App\Models\StudentFeeSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TuitionVoucherDistributionService
{
    public function rebalance(FeeVoucher $preferredVoucher, ?int $actorId = null): void
    {
        DB::transaction(function () use ($preferredVoucher, $actorId): void {
            $preferredVoucher = FeeVoucher::query()
                ->with('items.feeHead')
                ->lockForUpdate()
                ->findOrFail($preferredVoucher->id);

            if (! $this->isTuitionVoucher($preferredVoucher)) {
                return;
            }

            $account = StudentFeeAccount::query()->lockForUpdate()->find($preferredVoucher->student_fee_account_id);
            $admission = $account?->admission;

            if (! $account || ! $admission || empty($admission->custom_installments)) {
                return;
            }

            $vouchers = FeeVoucher::query()
                ->where('student_fee_account_id', $account->id)
                ->where('admission_id', $admission->id)
                ->where('voucher_type', 'monthly_installment')
                ->whereNotIn('status', ['cancelled', 'void'])
                ->with(['items.feeHead', 'payments', 'installment'])
                ->orderBy('due_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->filter(fn (FeeVoucher $voucher): bool => $this->isTuitionVoucher($voucher))
                ->values();

            $targetPaisa = $this->toPaisa(max(
                0,
                (float) $admission->custom_tuition_fee
                    - (float) $admission->concession_amount
                    - (float) $admission->custom_admission_fee,
            ));
            $fixed = $vouchers->filter(fn (FeeVoucher $voucher): bool => $voucher->is($preferredVoucher) || $this->hasPayment($voucher)
            );
            $adjustable = $vouchers->reject(fn (FeeVoucher $voucher): bool => $fixed->contains('id', $voucher->id))->values();
            $fixedPaisa = $fixed->sum(fn (FeeVoucher $voucher): int => $this->toPaisa($voucher->total_amount));
            $remainingPaisa = $targetPaisa - $fixedPaisa;

            if ($remainingPaisa < 0 || ($adjustable->isEmpty() && $remainingPaisa !== 0)) {
                throw ValidationException::withMessages([
                    'data.items' => 'This tuition amount exceeds the remaining admission tuition. Reduce it before saving.',
                ]);
            }

            if ($adjustable->isNotEmpty()) {
                $base = intdiv($remainingPaisa, $adjustable->count());
                $remainder = $remainingPaisa - ($base * $adjustable->count());

                foreach ($adjustable as $index => $voucher) {
                    $this->setVoucherAmount($voucher, $base + ($index === $adjustable->count() - 1 ? $remainder : 0));
                }
            }

            $this->synchronizeAdmissionSchedule($account, $actorId);
        });
    }

    private function isTuitionVoucher(FeeVoucher $voucher): bool
    {
        return $voucher->voucher_type === 'monthly_installment'
            && $voucher->items->contains(fn ($item): bool => ! in_array($item->adjustment_type, ['credit', 'discount'], true)
                && $item->feeHead?->category === 'tuition'
            );
    }

    private function hasPayment(FeeVoucher $voucher): bool
    {
        return (float) $voucher->paid_amount > 0
            || $voucher->payments->where('status', 'paid')->sum('amount') > 0;
    }

    private function setVoucherAmount(FeeVoucher $voucher, int $amountPaisa): void
    {
        $amount = number_format($amountPaisa / 100, 2, '.', '');
        $item = $voucher->items->first(fn ($item): bool => ! in_array($item->adjustment_type, ['credit', 'discount'], true)
            && $item->feeHead?->category === 'tuition'
        );
        $item?->update(['quantity' => 1, 'unit_amount' => $amount, 'amount' => $amount]);
        $voucher->refresh();
        $voucher->update(FeeVoucherCalculator::calculate($voucher));

        if ($voucher->installment) {
            $voucher->installment->update([
                'title' => $voucher->title,
                'due_date' => $voucher->due_date,
                'gross_paisa' => $amountPaisa,
                'concession_paisa' => 0,
                'net_paisa' => $amountPaisa,
            ]);
        }
    }

    private function synchronizeAdmissionSchedule(StudentFeeAccount $account, ?int $actorId): void
    {
        $vouchers = FeeVoucher::query()
            ->where('student_fee_account_id', $account->id)
            ->where('voucher_type', 'monthly_installment')
            ->whereNotIn('status', ['cancelled', 'void'])
            ->with('items.feeHead')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get()
            ->filter(fn (FeeVoucher $voucher): bool => $this->isTuitionVoucher($voucher))
            ->values();
        $schedule = $vouchers->map(fn (FeeVoucher $voucher, int $index): array => [
            'number' => $index + 1,
            'title' => $voucher->title,
            'due_date' => $voucher->due_date->toDateString(),
            'gross_paisa' => $this->toPaisa($voucher->subtotal),
            'concession_paisa' => 0,
            'net_paisa' => $this->toPaisa($voucher->total_amount),
        ]);

        $account->admission->update([
            'custom_installment_count' => $schedule->count(),
            'custom_installment_start_date' => data_get($schedule->first(), 'due_date'),
            'custom_installments' => $schedule->map(fn (array $row): array => [
                'title' => $row['title'],
                'due_date' => $row['due_date'],
                'amount' => number_format($row['net_paisa'] / 100, 2, '.', ''),
            ])->all(),
        ]);

        StudentFeeSnapshot::where('student_id', $account->student_id)->update([
            'installment_count' => $schedule->count(),
            'installment_schedule' => $schedule->all(),
        ]);
        FeeVoucherService::recalculateAccountTotals($account);

        FeeVoucherAudit::create([
            'fee_voucher_id' => $vouchers->first()->id,
            'user_id' => $actorId,
            'action' => 'installments_redistributed',
            'new_values' => ['schedule' => $schedule->all()],
            'ip_address' => request()->ip(),
            'notes' => 'Remaining unpaid tuition was redistributed after a tuition voucher change.',
        ]);
    }

    private function toPaisa(float|string|null $amount): int
    {
        return app(InstallmentPlanGenerator::class)->toPaisa($amount ?? 0);
    }
}

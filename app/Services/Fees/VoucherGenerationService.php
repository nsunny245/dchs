<?php

namespace App\Services\Fees;

use App\Models\FeeVoucher;
use App\Models\Student;
use App\Models\StudentInstallment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VoucherGenerationService
{
    public function __construct(private readonly InstallmentPlanGenerator $installments) {}

    /**
     * Build a read-only voucher preview without creating financial records.
     *
     * @return array<int, array{number:int,title:string,due_date:string,gross_paisa:int,concession_paisa:int,net_paisa:int}>
     */
    public function previewPlan(array $data): array
    {
        $count = max(1, (int) ($data['installment_count'] ?? 1));
        $start = Carbon::parse($data['admission_date'] ?? now());
        $schedule = $this->installments->generate((string) ($data['tuition'] ?? 0), $count, $start);
        $oneTimePaisa = $this->installments->toPaisa($data['one_time'] ?? 0);
        $examPaisa = $this->installments->toPaisa($data['examination'] ?? 0);
        $remainingConcession = max(0, $this->installments->toPaisa($data['concession'] ?? 0));

        $schedule[0]['gross_paisa'] += $oneTimePaisa;
        $schedule[0]['net_paisa'] += $oneTimePaisa;

        foreach ($schedule as &$row) {
            $applied = min($remainingConcession, $row['net_paisa']);
            $row['concession_paisa'] = $applied;
            $row['net_paisa'] -= $applied;
            $remainingConcession -= $applied;
        }
        unset($row);

        if ($examPaisa > 0) {
            $applied = min($remainingConcession, $examPaisa);
            $schedule[] = [
                'number' => count($schedule) + 1,
                'title' => 'Examination Registration Dues',
                'due_date' => $start->copy()->addMonthsNoOverflow(5)->toDateString(),
                'gross_paisa' => $examPaisa,
                'concession_paisa' => $applied,
                'net_paisa' => $examPaisa - $applied,
            ];
        }

        return $schedule;
    }

    /**
     * @param  Collection<int, FeeVoucher>  $vouchers
     */
    public function linkToInstallment(
        Collection $vouchers,
        StudentInstallment $installment,
        array $scheduleRow,
    ): void {
        $voucher = $vouchers->get($installment->installment_number - 1);

        if (! $voucher) {
            return;
        }

        $amount = number_format($scheduleRow['net_paisa'] / 100, 2, '.', '');
        $voucher->update([
            'installment_id' => $installment->id,
            'due_date' => $scheduleRow['due_date'],
            'total_amount' => $amount,
            'balance_amount' => $amount,
            'metadata' => array_merge($voucher->metadata ?? [], [
                'installment_number' => $installment->installment_number,
                'generated_from_finalized_admission' => true,
            ]),
        ]);
    }

    /**
     * Previewing is read-only and never creates vouchers.
     *
     * @return Collection<int, FeeVoucher>
     */
    public function preview(Student $student): Collection
    {
        return FeeVoucher::query()
            ->where('student_id', $student->id)
            ->with('items')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
    }
}

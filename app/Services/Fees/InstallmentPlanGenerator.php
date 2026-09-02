<?php

namespace App\Services\Fees;

use Carbon\CarbonInterface;
use InvalidArgumentException;

class InstallmentPlanGenerator
{
    /**
     * Normalize already itemized voucher charges to an exact account balance.
     *
     * @param  array<int, array{title:string,due_date:string,amount:string|int|float}>  $charges
     * @return array<int, array{number:int,title:string,due_date:string,gross_paisa:int,concession_paisa:int,net_paisa:int}>
     */
    public function normalize(array $charges, string|int|float $expectedTotal): array
    {
        if ($charges === []) {
            throw new InvalidArgumentException('At least one installment charge is required.');
        }

        $schedule = [];

        foreach (array_values($charges) as $index => $charge) {
            $amount = $this->toPaisa($charge['amount']);
            $schedule[] = [
                'number' => $index + 1,
                'title' => $charge['title'],
                'due_date' => $charge['due_date'],
                'gross_paisa' => $amount,
                'concession_paisa' => 0,
                'net_paisa' => $amount,
            ];
        }

        $difference = $this->toPaisa($expectedTotal) - array_sum(array_column($schedule, 'net_paisa'));
        $final = array_key_last($schedule);
        $schedule[$final]['gross_paisa'] += $difference;
        $schedule[$final]['net_paisa'] += $difference;

        if ($schedule[$final]['net_paisa'] < 0) {
            throw new InvalidArgumentException('Concession exceeds the payable installment charges.');
        }

        return $schedule;
    }

    /**
     * @return array<int, array{number:int,title:string,due_date:string,gross_paisa:int,concession_paisa:int,net_paisa:int}>
     */
    public function generate(string|int|float $netPayable, int $count, CarbonInterface $firstDueDate): array
    {
        if ($count < 1) {
            throw new InvalidArgumentException('Installment count must be at least one.');
        }

        $totalPaisa = $this->toPaisa($netPayable);
        $base = intdiv($totalPaisa, $count);
        $remainder = $totalPaisa - ($base * $count);
        $schedule = [];

        for ($number = 1; $number <= $count; $number++) {
            $amount = $base + ($number === $count ? $remainder : 0);
            $schedule[] = [
                'number' => $number,
                'title' => "Tuition Installment #{$number}",
                'due_date' => $firstDueDate->copy()->addMonthsNoOverflow($number - 1)->toDateString(),
                'gross_paisa' => $amount,
                'concession_paisa' => 0,
                'net_paisa' => $amount,
            ];
        }

        return $schedule;
    }

    public function toPaisa(string|int|float $amount): int
    {
        if (is_int($amount)) {
            return $amount * 100;
        }

        $normalized = trim((string) $amount);

        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $normalized)) {
            throw new InvalidArgumentException('Money must be a valid decimal amount.');
        }

        $negative = str_starts_with($normalized, '-');
        $normalized = ltrim($normalized, '-');
        [$rupees, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $fraction = substr(str_pad($fraction, 3, '0'), 0, 3);
        $paisa = ((int) $rupees * 100) + (int) substr($fraction, 0, 2);

        if ((int) $fraction[2] >= 5) {
            $paisa++;
        }

        return $negative ? -$paisa : $paisa;
    }
}

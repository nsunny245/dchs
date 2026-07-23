<?php

namespace App\Services\Fees;

use InvalidArgumentException;

class ConcessionCalculator
{
    public function __construct(private readonly InstallmentPlanGenerator $money) {}

    public function calculate(string|int|float $package, string $valueType, string|int|float $value): int
    {
        $packagePaisa = $this->money->toPaisa($package);

        if ($valueType === 'fixed') {
            return min($packagePaisa, max(0, $this->money->toPaisa($value)));
        }

        if ($valueType !== 'percentage') {
            throw new InvalidArgumentException('Unsupported concession calculation method.');
        }

        $basisPoints = $this->money->toPaisa($value);

        if ($basisPoints < 0 || $basisPoints > 10_000) {
            throw new InvalidArgumentException('Percentage concession must be between 0 and 100.');
        }

        return intdiv(($packagePaisa * $basisPoints) + 5_000, 10_000);
    }
}

<?php

namespace App\Filament\Resources\FeeVoucherResource\Pages;

use App\Filament\Resources\FeeVoucherResource;
use App\Services\Fees\FeeVoucherCalculator;
use App\Services\Fees\FeeVoucherService;
use App\Services\Fees\TuitionVoucherDistributionService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeeVoucher extends EditRecord
{
    protected static string $resource = FeeVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $voucher = $this->record;

        $voucher->update([
            'edit_request_status' => null,
            'edit_request_reason' => null,
            'edit_requested_by' => null,
        ]);

        $totals = FeeVoucherCalculator::calculate($voucher);
        $voucher->update($totals);

        if ($voucher->feeAccount) {
            app(TuitionVoucherDistributionService::class)->rebalance(
                $voucher,
                filament()->auth()->id(),
            );
            FeeVoucherService::recalculateAccountTotals($voucher->feeAccount);
        }
    }
}

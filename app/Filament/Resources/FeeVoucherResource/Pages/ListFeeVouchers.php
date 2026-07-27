<?php

namespace App\Filament\Resources\FeeVoucherResource\Pages;

use App\Filament\Resources\FeeVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeeVouchers extends ListRecords
{
    protected static string $resource = FeeVoucherResource::class;

    protected static string $view = 'filament.resources.fee-voucher-resource.pages.list-fee-vouchers';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CourseVoucherPrintWidget::class,
        ];
    }
}

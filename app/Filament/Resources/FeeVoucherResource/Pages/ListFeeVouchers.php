<?php

namespace App\Filament\Resources\FeeVoucherResource\Pages;

use App\Filament\Resources\FeeVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeeVouchers extends ListRecords
{
    protected static string $resource = FeeVoucherResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CourseVoucherPrintWidget::class,
        ];
    }
}

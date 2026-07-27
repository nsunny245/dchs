<?php

namespace App\Filament\Resources\FeeCollectionResource\Pages;

use App\Filament\Resources\FeeCollectionResource;
use App\Filament\Widgets\FeeCollectionOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListFeeCollections extends ListRecords
{
    protected static string $resource = FeeCollectionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            FeeCollectionOverviewWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('printAllCampusVouchers')
                ->label('Print All Campus Vouchers')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(route('fee-vouchers.print.campus-monthly'))
                ->openUrlInNewTab(),
        ];
    }
}

<?php

namespace App\Filament\Resources\FeeVoucherResource\Pages;

use App\Filament\Resources\FeeVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeeVouchers extends ListRecords
{
    protected static string $resource = FeeVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

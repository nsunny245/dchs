<?php

namespace App\Filament\Resources\FeeVoucherResource\Pages;

use App\Filament\Resources\FeeVoucherResource;
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
}

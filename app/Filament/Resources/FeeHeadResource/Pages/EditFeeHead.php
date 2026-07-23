<?php

namespace App\Filament\Resources\FeeHeadResource\Pages;

use App\Filament\Resources\FeeHeadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeeHead extends EditRecord
{
    protected static string $resource = FeeHeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

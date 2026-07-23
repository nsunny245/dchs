<?php

namespace App\Filament\Resources\FeeHeadResource\Pages;

use App\Filament\Resources\FeeHeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeeHeads extends ListRecords
{
    protected static string $resource = FeeHeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

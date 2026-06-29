<?php

namespace App\Filament\Resources\FranchisorResource\Pages;

use App\Filament\Resources\FranchisorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFranchisors extends ListRecords
{
    protected static string $resource = FranchisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

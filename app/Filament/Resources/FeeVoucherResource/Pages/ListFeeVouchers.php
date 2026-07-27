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
        $actions = [
            Actions\CreateAction::make(),
        ];

        if (filament()->getCurrentPanel()?->getId() === 'campus') {
            $actions[] = Actions\Action::make('printAllCampusVouchers')
                ->label('Print All Campus Vouchers')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(route('fee-vouchers.print.campus-monthly'))
                ->openUrlInNewTab();
        } else {
            $campuses = \App\Models\Campus::all();
            $campusActions = [];
            foreach ($campuses as $campus) {
                $campusActions[] = Actions\Action::make("printCampusVouchers_{$campus->id}")
                    ->label("{$campus->name}")
                    ->icon('heroicon-o-printer')
                    ->url(route('fee-vouchers.print.campus-monthly', ['campus_id' => $campus->id]))
                    ->openUrlInNewTab();
            }

            if (!empty($campusActions)) {
                $actions[] = Actions\ActionGroup::make($campusActions)
                    ->label('Print Campus Vouchers')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->button();
            }
        }

        return $actions;
    }
}

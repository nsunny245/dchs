<?php

namespace App\Filament\Resources\AdmissionResource\Pages;

use App\Filament\Resources\AdmissionResource;
use App\Models\AdmissionDraft;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListAdmissions extends ListRecords
{
    protected static string $resource = AdmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('resumeDraft')
                ->label('Resume Draft')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Select::make('draft')
                        ->label('Saved admission draft')
                        ->options(fn () => AdmissionDraft::where('created_by', filament()->auth()->id())
                            ->where('status', 'draft')
                            ->latest('last_saved_at')
                            ->get()
                            ->mapWithKeys(fn ($draft) => [
                                $draft->uuid => ($draft->payload['applicant_name'] ?? 'Unnamed draft').' — '.optional($draft->last_saved_at)->diffForHumans(),
                            ]))
                        ->searchable()
                        ->required(),
                ])
                ->action(fn (array $data) => redirect(AdmissionResource::getUrl('create', ['draft' => $data['draft']]))),
        ];
    }
}

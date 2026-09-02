<?php

namespace App\Filament\Resources\StaffResource\RelationManagers;

use App\Support\StaffOnboardingOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Staff Documents — Upload Missing Files Anytime';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('document_type')
                ->label('Document Type')
                ->options(StaffOnboardingOptions::documentTypes())
                ->required()
                ->native(false),
            Forms\Components\FileUpload::make('path')
                ->label('Document File')
                ->disk('public')
                ->directory('staff/documents')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->required(fn (string $operation): bool => $operation === 'create')
                ->openable()
                ->downloadable(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending Verification',
                    'verified' => 'Verified',
                    'rejected' => 'Rejected',
                ])
                ->default('pending')
                ->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Document')
                    ->formatStateUsing(fn (string $state): string => StaffOnboardingOptions::documentTypes()[$state] ?? str($state)->replace('_', ' ')->title())
                    ->searchable(),
                Tables\Columns\TextColumn::make('stored_filename')
                    ->label('File')
                    ->limit(40)
                    ->placeholder('No file'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Upload Missing Document')
                    ->mutateFormDataUsing(fn (array $data): array => $this->prepareDocumentData($data)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('open')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Model $record): string => Storage::disk($record->disk ?: 'public')->url($record->path))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make()
                        ->mutateFormDataUsing(fn (array $data): array => $this->prepareDocumentData($data)),
                    Tables\Actions\DeleteAction::make(),
                ])->label('Actions')->button()->color('primary'),
            ])
            ->emptyStateHeading('All requested documents are currently missing')
            ->emptyStateDescription('This does not block the staff record. Upload each file when the teacher provides it.')
            ->emptyStateIcon('heroicon-o-document-plus');
    }

    private function prepareDocumentData(array $data): array
    {
        $type = $data['document_type'] ?? 'document';
        $path = $data['path'] ?? null;

        $data['title'] = StaffOnboardingOptions::documentTypes()[$type] ?? str($type)->replace('_', ' ')->title();
        $data['disk'] = 'public';
        $data['stored_filename'] = is_string($path) ? basename($path) : null;
        $data['uploaded_by'] = auth()->id();

        return $data;
    }
}

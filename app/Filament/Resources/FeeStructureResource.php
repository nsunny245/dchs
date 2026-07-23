<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeeStructureResource\Pages;
use App\Models\FeeStructure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeeStructureResource extends Resource
{
    protected static ?string $model = FeeStructure::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = filament()->auth()->user();
        if (!$user) return false;

        return $user->email === 'admin@admin.com' 
            || $user->hasRole('Super Admin') 
            || $user->campus_id === null 
            || filament()->getCurrentPanel()?->getId() === 'admin';
    }

    public static function canViewAny(): bool
    {
        $user = filament()->auth()->user();
        if (!$user) return false;

        return $user->email === 'admin@admin.com' 
            || $user->hasRole('Super Admin') 
            || $user->campus_id === null 
            || filament()->getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Fee Structure Configuration')
                    ->description('Define the global course fee for this program')
                    ->schema([
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'name')
                            ->label('Assigned Course / Program')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Forms\Components\TextInput::make('total_fee')
                            ->label('Course Fee (Total Tuition)')
                            ->numeric()
                            ->prefix('PKR')
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('installment_count')
                            ->label('Number of Tuition Installments')
                            ->numeric()
                            ->default(12)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Target Course / Program')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_fee')
                    ->label('Course Fee')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('installment_count')
                    ->label('Default Installments')
                    ->sortable(),
            ])
            ->filters([
                // No filters needed since it is a simple list
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeStructures::route('/'),
            'create' => Pages\CreateFeeStructure::route('/create'),
            'edit' => Pages\EditFeeStructure::route('/{record}/edit'),
        ];
    }
}

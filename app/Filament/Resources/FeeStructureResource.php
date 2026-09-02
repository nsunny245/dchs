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
        if (! $user) {
            return false;
        }

        return $user->email === 'admin@admin.com'
            || $user->hasRole('Super Admin')
            || $user->campus_id === null
            || filament()->getCurrentPanel()?->getId() === 'admin';
    }

    public static function canViewAny(): bool
    {
        $user = filament()->auth()->user();
        if (! $user) {
            return false;
        }

        return $user->email === 'admin@admin.com'
            || $user->hasRole('Super Admin')
            || $user->campus_id === null
            || filament()->getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return filament()->auth()->user()?->hasRole('Super Admin') ?? false;
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
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Plan Name')
                            ->placeholder('e.g. LHV 2026 Official Plan'),
                        Forms\Components\Select::make('campus_id')
                            ->relationship('campus', 'name')
                            ->label('Campus')
                            ->nullable(),
                        Forms\Components\Select::make('academic_session_id')
                            ->relationship('academicSession', 'name')
                            ->label('Academic Session')
                            ->nullable(),
                        Forms\Components\Select::make('shift')
                            ->options(['morning' => 'Morning', 'evening' => 'Evening'])
                            ->nullable(),
                        Forms\Components\TextInput::make('total_fee')
                            ->label('Course Fee (Total Tuition)')
                            ->numeric()
                            ->prefix('PKR')
                            ->live(onBlur: true)
                            ->required(),
                        Forms\Components\TextInput::make('installment_count')
                            ->label('Number of Tuition Installments')
                            ->numeric()
                            ->default(12)
                            ->required(),
                        Forms\Components\TextInput::make('version')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\DatePicker::make('effective_date'),
                        Forms\Components\DatePicker::make('expiry_date'),
                        Forms\Components\Select::make('status')
                            ->options(['draft' => 'Draft', 'active' => 'Active', 'retired' => 'Retired'])
                            ->default('active')
                            ->required(),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('campus.name')->default('All campuses'),
                Tables\Columns\TextColumn::make('academicSession.name')->label('Session')->default('All sessions'),
                Tables\Columns\TextColumn::make('version')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn () => filament()->auth()->user()->hasRole('Super Admin')),
                ])->label('Actions')->button()->color('primary'),
            ])
            ->bulkActions([]);
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

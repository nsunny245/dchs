<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuperAdminFranchisorPaymentResource\Pages;
use App\Filament\Resources\SuperAdminFranchisorPaymentResource\RelationManagers;
use App\Models\FranchisorStudentPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SuperAdminFranchisorPaymentResource extends Resource
{
    protected static ?string $model = FranchisorStudentPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Franchise Management';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Franchisor Seat Payments';
    protected static ?string $modelLabel = 'Franchisor Seat Payment';
    protected static ?string $pluralModelLabel = 'Franchisor Seat Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Franchisor Payment Ledger')
                    ->schema([
                        Forms\Components\Select::make('franchisor_id')
                            ->relationship('franchisor', 'name')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('admission_id')
                            ->relationship('admission', 'applicant_name')
                            ->label('Student Name')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->required(),
                        Forms\Components\TextInput::make('paid_amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partially Paid',
                                'paid' => 'Fully Paid',
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\TextColumn::make('admission.applicant_name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('franchisor.name')
                    ->label('Franchisor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Outstanding')
                    ->state(fn (FranchisorStudentPayment $record) => $record->total_amount - $record->paid_amount)
                    ->money('PKR')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partially Paid',
                        'paid' => 'Fully Paid',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('franchisor_id')
                    ->relationship('franchisor', 'name')
                    ->label('Franchisor'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partially Paid',
                        'paid' => 'Fully Paid',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\SuperAdminInstallmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuperAdminFranchisorPayments::route('/'),
            'edit' => Pages\EditSuperAdminFranchisorPayment::route('/{record}/edit'),
        ];
    }
}

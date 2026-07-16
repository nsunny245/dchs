<?php

namespace App\Filament\Franchisor\Resources;

use App\Filament\Franchisor\Resources\FranchisorPaymentResource\Pages;
use App\Filament\Franchisor\Resources\FranchisorPaymentResource\RelationManagers;
use App\Models\FranchisorStudentPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FranchisorPaymentResource extends Resource
{
    protected static ?string $model = FranchisorStudentPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Seat Payments';
    protected static ?string $modelLabel = 'Seat Payment';
    protected static ?string $pluralModelLabel = 'Seat Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('franchisor_id')
                            ->relationship('franchisor', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('admission_id')
                            ->relationship('admission', 'applicant_name')
                            ->label('Student Name')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->disabled(),
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
                            ->disabled()
                            ->columnSpanFull(),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partially Paid',
                        'paid' => 'Fully Paid',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $type = FranchisorAdmissionResource::getFranchisorType();

        if ($type) {
            $query->whereHas('franchisor', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InstallmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFranchisorPayments::route('/'),
            'view' => Pages\ViewFranchisorPayment::route('/{record}'),
        ];
    }
}

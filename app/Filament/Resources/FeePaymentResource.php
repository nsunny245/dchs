<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeePaymentResource\Pages;
use App\Models\FeePayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeePaymentResource extends Resource
{
    protected static ?string $model = FeePayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Financial Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Record')
                    ->schema([
                        Forms\Components\Select::make('campus_id')
                            ->relationship('campus', 'name')
                            ->required()
                            ->default(fn () => auth()->user()->campus_id)
                            ->disabled(fn () => !auth()->user()->hasRole('Super Admin'))
                            ->dehydrated(),
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('fee_structure_id')
                            ->relationship('feeStructure', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Year: {$record->academic_year} | {$record->course->name} (Total: {$record->total_fee} PKR)")
                            ->required(),
                        Forms\Components\TextInput::make('installment_no')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->required(),
                        Forms\Components\DatePicker::make('paid_date'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'partial' => 'Partial',
                            ])
                            ->default('unpaid')
                            ->required(),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'cheque' => 'Cheque',
                            ]),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID / Receipt #')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.full_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('feeStructure.course.name')
                    ->label('Course'),
                Tables\Columns\TextColumn::make('installment_no')
                    ->label('Installment #'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        'overdue' => 'warning',
                        'partial' => 'info',
                    }),
                Tables\Columns\TextColumn::make('payment_method')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Receipt #'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('campus', 'name')
                    ->hidden(fn () => !auth()->user()->hasRole('Super Admin')),
                Tables\Filters\SelectFilter::make('payment_method'),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadReceipt')
                    ->label('Receipt')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => route('pdf.fee-receipt', $record))
                    ->openUrlInNewTab(),
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
        $query = parent::getEloquentQuery();
        
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('campus_id', auth()->user()->campus_id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeePayments::route('/'),
            'create' => Pages\CreateFeePayment::route('/create'),
            'edit' => Pages\EditFeePayment::route('/{record}/edit'),
        ];
    }
}

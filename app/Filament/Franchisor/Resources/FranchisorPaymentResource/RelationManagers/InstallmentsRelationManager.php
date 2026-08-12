<?php

namespace App\Filament\Franchisor\Resources\FranchisorPaymentResource\RelationManagers;

use App\Models\FranchisorPaymentInstallment;
use App\Support\DashboardImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'installments';

    protected static ?string $title = 'Installments Plan';

    protected function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('is_published', true);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->disabled()
                    ->numeric()
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'pending' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Unpaid',
                        'pending' => 'Pending Verification',
                        'paid' => 'Verified Paid',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Txn ID')
                    ->placeholder('-'),
                Tables\Columns\ImageColumn::make('receipt_path')
                    ->label('Receipt')
                    ->getStateUsing(fn (FranchisorPaymentInstallment $record): ?string => DashboardImage::url($record->receipt_path))
                    ->square()
                    ->size(44)
                    ->placeholder('No Receipt'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('submitProof')
                    ->label('Submit Receipt')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->visible(fn (FranchisorPaymentInstallment $record) => $record->status === 'unpaid')
                    ->form([
                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer / Deposit',
                                'cheque' => 'Cheque',
                                'online' => 'Online App Transfer',
                                'cash' => 'Cash',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID / Reference')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('receipt_path')
                            ->label('Upload Receipt Image/PDF')
                            ->directory('franchisor-receipts')
                            ->disk('public')
                            ->image()
                            ->maxSize(2048)
                            ->required(),
                    ])
                    ->action(function (FranchisorPaymentInstallment $record, array $data) {
                        $record->update([
                            'payment_method' => $data['payment_method'],
                            'transaction_id' => $data['transaction_id'],
                            'receipt_path' => $data['receipt_path'],
                            'status' => 'pending',
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Receipt Submitted')
                            ->success()
                            ->body('Payment proof has been submitted and is awaiting admin verification.')
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}

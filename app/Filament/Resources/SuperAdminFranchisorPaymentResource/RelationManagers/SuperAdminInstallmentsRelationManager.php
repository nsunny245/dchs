<?php

namespace App\Filament\Resources\SuperAdminFranchisorPaymentResource\RelationManagers;

use App\Models\FranchisorPaymentInstallment;
use App\Support\DashboardImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SuperAdminInstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'installments';

    protected static ?string $title = 'Franchisor Installment Schedule';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->required(),
                Forms\Components\DatePicker::make('due_date'),
                Forms\Components\Select::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'pending' => 'Pending Verification',
                        'paid' => 'Verified Paid',
                    ])
                    ->default('unpaid')
                    ->required(),
                Forms\Components\DatePicker::make('paid_date'),
                Forms\Components\TextInput::make('payment_method')
                    ->maxLength(255),
                Forms\Components\TextInput::make('transaction_id')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('receipt_path')
                    ->directory('franchisor-receipts')
                    ->disk('public'),
                Forms\Components\Toggle::make('is_published')
                    ->label('Publish immediately to Franchisor')
                    ->default(true),
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
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Sent to Franchisor'),
                Tables\Columns\TextColumn::make('payment_method')
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('sendToFranchisor')
                    ->label('Send')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (FranchisorPaymentInstallment $record) => !$record->is_published)
                    ->action(function (FranchisorPaymentInstallment $record) {
                        $record->update([
                            'is_published' => true,
                         ]);

                         \Filament\Notifications\Notification::make()
                             ->title('Sent to Franchisor')
                             ->success()
                             ->body('This payment installment has been published to the franchisor dashboard.')
                             ->send();
                    }),
                Tables\Actions\Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(fn (FranchisorPaymentInstallment $record) => $record->status !== 'paid')
                    ->form([
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'cash' => 'Cash',
                                'cheque' => 'Cheque',
                                'online_deposit' => 'Online Deposit',
                            ])
                            ->required()
                            ->default('bank_transfer'),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction Reference / ID')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('paid_date')
                            ->label('Payment Date')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (FranchisorPaymentInstallment $record, array $data) {
                        $record->update([
                            'status' => 'paid',
                            'payment_method' => $data['payment_method'],
                            'transaction_id' => $data['transaction_id'] ?? null,
                            'paid_date' => $data['paid_date'],
                            'is_published' => true, // Auto publish once paid
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Payment Recorded')
                            ->success()
                            ->body('The payment has been successfully recorded and applied.')
                            ->send();
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (FranchisorPaymentInstallment $record) => $record->status === 'pending')
                    ->action(function (FranchisorPaymentInstallment $record) {
                        $record->update([
                            'status' => 'paid',
                            'paid_date' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Payment Verified')
                            ->success()
                            ->body('The installment payment has been approved and marked as verified paid.')
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (FranchisorPaymentInstallment $record) => $record->status === 'pending')
                    ->action(function (FranchisorPaymentInstallment $record) {
                        $record->update([
                            'status' => 'unpaid',
                            'payment_method' => null,
                            'transaction_id' => null,
                            'receipt_path' => null,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Payment Rejected')
                            ->danger()
                            ->body('The installment payment has been rejected.')
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}

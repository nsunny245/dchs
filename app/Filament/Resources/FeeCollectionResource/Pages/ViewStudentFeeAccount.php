<?php

namespace App\Filament\Resources\FeeCollectionResource\Pages;

use App\Filament\Resources\FeeCollectionResource;
use App\Models\FeeVoucher;
use App\Models\FeePayment;
use App\Models\PaymentAllocation;
use App\Services\Fees\FeeVoucherService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;

class ViewStudentFeeAccount extends ViewRecord
{
    protected static string $resource = FeeCollectionResource::class;

    protected static string $view = 'filament.resources.fee-collection-resource.pages.view-student-fee-account';

    public function getTitle(): string
    {
        return "Fee Account: " . $this->record->student->full_name;
    }

    protected function getActions(): array
    {
        return [
            \Filament\Actions\Action::make('collectPayment')
                ->label('Collect Payment')
                ->color('success')
                ->form(function (array $arguments) {
                    $voucherId = $arguments['voucher_id'] ?? null;
                    $voucher = $voucherId ? FeeVoucher::find($voucherId) : null;
                    $defaultAmount = $voucher ? $voucher->balance_amount : 0;

                    return [
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Placeholder::make('student_summary')
                                    ->label('Account Balance Summary')
                                    ->content(fn () => new HtmlString(sprintf(
                                        '<div class="p-3 bg-slate-50 dark:bg-slate-800 border dark:border-slate-700 rounded space-y-1 text-sm">
                                            <div><strong>Student Name:</strong> %s</div>
                                            <div><strong>Net Payable:</strong> PKR %s</div>
                                            <div><strong>Amount Paid:</strong> PKR %s</div>
                                            <div><strong>Current Balance:</strong> <strong class="text-rose-600">PKR %s</strong></div>
                                        </div>',
                                        $this->record->student->full_name,
                                        number_format($this->record->net_payable, 2),
                                        number_format($this->record->amount_paid, 2),
                                        number_format($this->record->balance, 2)
                                    )))
                                    ->columnSpanFull(),

                                Forms\Components\Hidden::make('target_voucher_id')
                                    ->default($voucherId),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Amount Received')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->default($defaultAmount)
                                    ->required()
                                    ->minValue(1),
                                Forms\Components\DatePicker::make('payment_date')
                                    ->label('Collection Date')
                                    ->default(now())
                                    ->required(),
                                Forms\Components\Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->options([
                                        'cash' => 'Cash',
                                        'bank' => 'Bank Deposit / Transfer',
                                        'online' => 'Online EasyPaisa/JazzCash',
                                        'cheque' => 'Cheque',
                                    ])
                                    ->default('cash')
                                    ->required(),
                                Forms\Components\TextInput::make('transaction_reference')
                                    ->label('Transaction ID / Cheque #')
                                    ->placeholder('e.g. TXN10398102'),
                                Forms\Components\TextInput::make('bank_account')
                                    ->label('Deposited Bank Account')
                                    ->placeholder('e.g. Allied Bank A/C 12345'),
                                Forms\Components\Select::make('allocation_rule')
                                    ->label('Payment Allocation Logic')
                                    ->options([
                                        'selected_voucher' => 'Apply to Selected Voucher Only',
                                        'oldest_first' => 'Oldest Outstanding First (Automatic)',
                                        'advance' => 'Apply as Advance Credit',
                                    ])
                                    ->default('selected_voucher')
                                    ->live()
                                    ->required(),
                                Forms\Components\Select::make('selected_voucher_id')
                                    ->label('Target Voucher')
                                    ->options(function () {
                                         return FeeVoucher::where('student_fee_account_id', $this->record->id)
                                             ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
                                             ->pluck('title', 'id');
                                    })
                                    ->default($voucherId)
                                    ->visible(fn (Forms\Get $get) => $get('allocation_rule') === 'selected_voucher')
                                    ->required(fn (Forms\Get $get) => $get('allocation_rule') === 'selected_voucher'),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Cashier Notes')
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('office_copy')
                                    ->label('Voucher Office Copy')
                                    ->directory('payment-receipts')
                                    ->image()
                                    ->maxSize(5120)
                                    ->columnSpanFull()
                                    ->nullable(),
                            ])
                    ];
                })
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $amount = (float)$data['amount'];
                        $rule = $data['allocation_rule'];
                        $record = $this->record;

                        $remainingAmount = $amount;

                        if ($rule === 'selected_voucher') {
                            $voucherId = $data['selected_voucher_id'] ?? $data['target_voucher_id'];
                            $voucher = FeeVoucher::find($voucherId);
                            if ($voucher) {
                                $allocated = min($remainingAmount, (float)$voucher->balance_amount);
                                
                                $paymentData = $data;
                                $paymentData['amount'] = $allocated;
                                
                                $payment = FeeVoucherService::recordPayment($voucher, $paymentData);
                                
                                PaymentAllocation::create([
                                    'payment_id' => $payment->id,
                                    'fee_voucher_id' => $voucher->id,
                                    'amount' => $allocated,
                                ]);
                                $remainingAmount -= $allocated;
                            }
                        } else {
                            $vouchers = FeeVoucher::where('student_fee_account_id', $record->id)
                                ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
                                ->orderBy('due_date', 'asc')
                                ->orderBy('sequence_no', 'asc')
                                ->get();

                            foreach ($vouchers as $voucher) {
                                if ($remainingAmount <= 0) break;

                                $allocated = min($remainingAmount, (float)$voucher->balance_amount);
                                
                                $paymentData = $data;
                                $paymentData['amount'] = $allocated;

                                $payment = FeeVoucherService::recordPayment($voucher, $paymentData);

                                PaymentAllocation::create([
                                    'payment_id' => $payment->id,
                                    'fee_voucher_id' => $voucher->id,
                                    'amount' => $allocated,
                                ]);

                                $remainingAmount -= $allocated;
                            }
                        }

                        if ($remainingAmount > 0) {
                            $upcomingVouchers = FeeVoucher::where('student_fee_account_id', $record->id)
                                ->where('status', 'upcoming')
                                ->orderBy('due_date', 'asc')
                                ->get();

                            foreach ($upcomingVouchers as $voucher) {
                                if ($remainingAmount <= 0) break;

                                $allocated = min($remainingAmount, (float)$voucher->balance_amount);
                                
                                $paymentData = $data;
                                $paymentData['amount'] = $allocated;

                                $payment = FeeVoucherService::recordPayment($voucher, $paymentData);

                                PaymentAllocation::create([
                                    'payment_id' => $payment->id,
                                    'fee_voucher_id' => $voucher->id,
                                    'amount' => $allocated,
                                ]);

                                $remainingAmount -= $allocated;
                            }
                        }
                    });

                    Notification::make()
                        ->title('Payment Collected')
                        ->body('Payment successfully received and allocated.')
                        ->success()
                        ->send();

                    $this->redirect(self::getResource()::getUrl('view', ['record' => $this->record]));
                })
        ];
    }

    protected function getViewData(): array
    {
        $vouchers = FeeVoucher::where('student_fee_account_id', $this->record->id)
            ->orderBy('sequence_no', 'asc')
            ->orderBy('due_date', 'asc')
            ->get();

        $payments = FeePayment::where('student_fee_account_id', $this->record->id)
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $nextVoucher = FeeVoucher::where('student_fee_account_id', $this->record->id)
            ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
            ->orderBy('due_date', 'asc')
            ->first();

        $overdue = FeeVoucher::where('student_fee_account_id', $this->record->id)
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
            ->sum('balance_amount');

        return [
            'vouchers' => $vouchers,
            'payments' => $payments,
            'nextVoucher' => $nextVoucher,
            'overdueAmount' => $overdue,
        ];
    }
}

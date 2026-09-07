<?php

namespace App\Filament\Resources\FeeCollectionResource\Pages;

use App\Filament\Resources\FeeCollectionResource;
use App\Models\FeePayment;
use App\Models\FeeVoucher;
use App\Models\PaymentAllocation;
use App\Services\Fees\AdmissionVoucherReconciliationService;
use App\Services\Fees\FeeVoucherService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class ViewStudentFeeAccount extends ViewRecord
{
    protected static string $resource = FeeCollectionResource::class;

    protected static string $view = 'filament.resources.fee-collection-resource.pages.view-student-fee-account';

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function getTitle(): string
    {
        return 'Fee Account: '.$this->record->student->full_name;
    }

    protected function getActions(): array
    {
        return [
            Action::make('collectPayment')
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
                                            ->orderBy('due_date')
                                            ->get()
                                            ->mapWithKeys(fn (FeeVoucher $voucher) => [
                                                $voucher->id => $voucher->title.' — Balance PKR '.number_format($voucher->balance_amount, 2),
                                            ]);
                                    })
                                    ->default($voucherId)
                                    ->visible(fn (Forms\Get $get) => $get('allocation_rule') === 'selected_voucher')
                                    ->required(fn (Forms\Get $get) => $get('allocation_rule') === 'selected_voucher'),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Cashier Notes')
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('office_copy')
                                    ->label('Voucher Office Copy')
                                    ->disk('public')
                                    ->directory('payment-receipts')
                                    ->image()
                                    ->maxSize(5120)
                                    ->columnSpanFull()
                                    ->nullable(),
                            ]),
                    ];
                })
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $amount = (float) $data['amount'];
                        $rule = $data['allocation_rule'];
                        $record = $this->record;

                        $remainingAmount = $amount;

                        if ($rule === 'selected_voucher') {
                            $voucherId = $data['selected_voucher_id'] ?? $data['target_voucher_id'];
                            $voucher = FeeVoucher::query()
                                ->where('student_fee_account_id', $record->id)
                                ->lockForUpdate()
                                ->find($voucherId);

                            if (! $voucher) {
                                throw ValidationException::withMessages([
                                    'data.selected_voucher_id' => 'Select a valid outstanding installment voucher.',
                                ]);
                            }

                            if ($remainingAmount > (float) $voucher->balance_amount) {
                                throw ValidationException::withMessages([
                                    'data.amount' => 'For a selected installment, the amount cannot exceed its balance of PKR '.number_format($voucher->balance_amount, 2).'.',
                                ]);
                            }

                            if ($remainingAmount > 0) {
                                $allocated = $remainingAmount;

                                $paymentData = $data;
                                $paymentData['amount'] = $allocated;

                                $payment = FeeVoucherService::recordPayment($voucher, $paymentData);

                                PaymentAllocation::create([
                                    'payment_id' => $payment->id,
                                    'fee_voucher_id' => $voucher->id,
                                    'amount' => $allocated,
                                ]);
                                $remainingAmount = 0;
                            }
                        } else {
                            $vouchers = FeeVoucher::where('student_fee_account_id', $record->id)
                                ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
                                ->orderBy('due_date', 'asc')
                                ->orderBy('sequence_no', 'asc')
                                ->get();

                            foreach ($vouchers as $voucher) {
                                if ($remainingAmount <= 0) {
                                    break;
                                }

                                $allocated = min($remainingAmount, (float) $voucher->balance_amount);

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
                                if ($remainingAmount <= 0) {
                                    break;
                                }

                                $allocated = min($remainingAmount, (float) $voucher->balance_amount);

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
                }),
            Action::make('syncAdmissionPlan')
                ->label('Sync From Admission Plan')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Synchronize admission installments?')
                ->modalDescription('This updates unpaid tuition vouchers to exactly match the titles, dates and amounts saved in the admission form. Any collected admission-fee payment is preserved. Paid tuition vouchers remain protected.')
                ->visible(fn (): bool => $this->record->admission !== null)
                ->action(function (): void {
                    try {
                        app(AdmissionVoucherReconciliationService::class)->reconcile(
                            $this->record,
                            filament()->auth()->id(),
                        );

                        Notification::make()
                            ->title('Admission vouchers synchronized')
                            ->body('The fee account now matches the saved admission installment schedule.')
                            ->success()
                            ->send();

                        $this->redirect(self::getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Synchronization was not applied')
                            ->body(collect($exception->errors())->flatten()->first() ?: $exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getViewData(): array
    {
        $vouchers = FeeVoucher::where('student_fee_account_id', $this->record->id)
            ->with('items.feeHead')
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        $activeVouchers = $vouchers->whereNotIn('status', ['cancelled', 'void'])->values();
        $tuitionVouchers = $activeVouchers
            ->filter(fn (FeeVoucher $voucher): bool => $voucher->isAdmissionTuitionInstallment())
            ->values();
        $admissionVoucher = $activeVouchers->firstWhere('voucher_type', 'new_enrollment');
        $savedAdmissionFee = (float) ($this->record->admission?->custom_admission_fee ?? 0);
        $savedSchedule = collect($this->record->admission?->custom_installments ?? [])
            ->filter(fn (array $row): bool => (float) ($row['amount'] ?? 0) > 0)
            ->values();
        $admissionFeeMatches = $savedAdmissionFee <= 0
            ? $admissionVoucher === null
            : $admissionVoucher !== null
                && abs($savedAdmissionFee - (float) $admissionVoucher->subtotal) < 0.01;
        $planMatches = $savedSchedule->isEmpty()
            || ($admissionFeeMatches
            && $savedSchedule->count() === $tuitionVouchers->count()
            && $savedSchedule->every(function (array $row, int $index) use ($tuitionVouchers): bool {
                $voucher = $tuitionVouchers->get($index);

                return $voucher
                    && trim((string) ($row['title'] ?? '')) === trim((string) $voucher->title)
                    && Carbon::parse($row['due_date'])->toDateString() === $voucher->due_date?->toDateString()
                    && abs((float) $row['amount'] - (float) $voucher->subtotal) < 0.01;
            }));

        // The admission plan area must mirror only the separate admission fee
        // and the active tuition rows saved by the admission wizard. Cancelled
        // legacy rows and independently-created fee-head vouchers belong outside
        // this schedule and must not inflate its count or totals.
        $planVouchers = collect([$admissionVoucher])
            ->filter()
            ->concat($tuitionVouchers)
            ->sortBy(fn (FeeVoucher $voucher): string => ($voucher->due_date?->format('Y-m-d') ?? '').'-'.str_pad((string) $voucher->id, 12, '0', STR_PAD_LEFT))
            ->values();
        $planVoucherIds = $planVouchers->pluck('id');

        $payments = FeePayment::where('student_fee_account_id', $this->record->id)
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $nextVoucher = FeeVoucher::where('student_fee_account_id', $this->record->id)
            ->whereIn('id', $planVoucherIds)
            ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
            ->orderBy('due_date', 'asc')
            ->first();

        $overdue = FeeVoucher::where('student_fee_account_id', $this->record->id)
            ->whereIn('id', $planVoucherIds)
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
            ->sum('balance_amount');

        return [
            'vouchers' => $planVouchers,
            'payments' => $payments,
            'nextVoucher' => $nextVoucher,
            'overdueAmount' => $overdue,
            'scheduledAmount' => (float) $planVouchers->sum('subtotal'),
            'voucherConcession' => (float) ($this->record->admission?->concession_amount ?? 0),
            'installmentCount' => $tuitionVouchers->count(),
            'planMatches' => $planMatches,
            'hasPaymentHistory' => $this->record->payments()->where('status', 'paid')->where('amount', '>', 0)->exists(),
            'savedScheduleTotal' => $savedAdmissionFee + (float) $savedSchedule->sum(fn (array $row): float => (float) $row['amount']),
        ];
    }
}

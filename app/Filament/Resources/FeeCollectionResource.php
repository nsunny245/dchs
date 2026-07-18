<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeeCollectionResource\Pages;
use App\Models\StudentFeeAccount;
use App\Models\StudentVoucher;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\RebuildAuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class FeeCollectionResource extends Resource
{
    protected static ?string $model = StudentFeeAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Fee Collection';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Fee Account Summary')
                    ->schema([
                        Forms\Components\Placeholder::make('student_info')
                            ->label('Student Details')
                            ->content(fn ($record) => $record ? "{$record->student->full_name} ({$record->student->enrollment_number})" : ''),
                        Forms\Components\Placeholder::make('original_fee')
                            ->label('Original Dues')
                            ->content(fn ($record) => $record ? 'PKR ' . number_format($record->original_fee, 2) : ''),
                        Forms\Components\Placeholder::make('concession_amount')
                            ->label('Approved Concession')
                            ->content(fn ($record) => $record ? 'PKR ' . number_format($record->concession_amount, 2) : ''),
                        Forms\Components\Placeholder::make('net_payable')
                            ->label('Net Payable')
                            ->content(fn ($record) => $record ? 'PKR ' . number_format($record->net_payable, 2) : ''),
                        Forms\Components\Placeholder::make('amount_paid')
                            ->label('Total Paid')
                            ->content(fn ($record) => $record ? 'PKR ' . number_format($record->amount_paid, 2) : ''),
                        Forms\Components\Placeholder::make('balance')
                            ->label('Outstanding Balance')
                            ->content(fn ($record) => $record ? 'PKR ' . number_format($record->balance, 2) : ''),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\ImageColumn::make('student.student_photo')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(url('images/default-avatar.png')),
                Tables\Columns\TextColumn::make('student.full_name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.enrollment_number')
                    ->label('Enrollment No')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.course.name')
                    ->label('Course')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_payable')
                    ->label('Total Net Fee')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('overdue_amount')
                    ->label('Overdue')
                    ->money('PKR')
                    ->state(function ($record) {
                        return StudentVoucher::where('student_fee_account_id', $record->id)
                            ->where('due_date', '<', now())
                            ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
                            ->sum('balance');
                    })
                    ->color('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('student.campus', 'name')
                    ->hidden(fn () => !filament()->auth()->user()->hasRole('Super Admin')),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('student.course', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('openFeeAccount')
                    ->label('Fee Account')
                    ->icon('heroicon-o-book-open')
                    ->color('info')
                    ->url(fn ($record) => self::getUrl('view', ['record' => $record])),

                Tables\Actions\Action::make('collectPayment')
                    ->label('Collect')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Placeholder::make('student_summary')
                                    ->label('Account Balance Summary')
                                    ->content(fn ($record) => new \Illuminate\Support\HtmlString(sprintf(
                                        '<div class="p-3 bg-slate-50 border rounded space-y-1">
                                            <div><strong>Net Payable:</strong> PKR %s</div>
                                            <div><strong>Amount Paid:</strong> PKR %s</div>
                                            <div><strong>Current Balance:</strong> <strong class="text-rose-600">PKR %s</strong></div>
                                        </div>',
                                        number_format($record->net_payable, 2),
                                        number_format($record->amount_paid, 2),
                                        number_format($record->balance, 2)
                                    )))
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Amount Received')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->required()
                                    ->minVal(1),
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
                                        'oldest_first' => 'Oldest Outstanding First (Automatic)',
                                        'selected_voucher' => 'Apply to Selected Voucher Only',
                                        'advance' => 'Apply as Advance Credit',
                                    ])
                                    ->default('oldest_first')
                                    ->live()
                                    ->required(),
                                Forms\Components\Select::make('selected_voucher_id')
                                    ->label('Target Voucher')
                                    ->options(function ($record) {
                                        return StudentVoucher::where('student_fee_account_id', $record->id)
                                            ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
                                            ->pluck('title', 'id');
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('allocation_rule') === 'selected_voucher')
                                    ->required(fn (Forms\Get $get) => $get('allocation_rule') === 'selected_voucher'),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Cashier Notes')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->action(function ($record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $amount = (float)$data['amount'];
                            $method = $data['payment_method'];
                            $ref = $data['transaction_reference'];
                            $bank = $data['bank_account'];
                            $date = $data['payment_date'];
                            $notes = $data['notes'];
                            $rule = $data['allocation_rule'];

                            // Generate receipt number
                            $receiptNumber = 'REC-' . strtoupper(Str::random(10));

                            // Create Payment transaction
                            $payment = Payment::create([
                                'student_id' => $record->student_id,
                                'student_fee_account_id' => $record->id,
                                'receipt_number' => $receiptNumber,
                                'amount' => $amount,
                                'payment_date' => $date,
                                'payment_method' => $method,
                                'transaction_reference' => $ref,
                                'bank_account' => $bank,
                                'notes' => $notes,
                                'collected_by' => filament()->auth()->id(),
                            ]);

                            $remainingAmount = $amount;

                            if ($rule === 'selected_voucher') {
                                $voucher = StudentVoucher::find($data['selected_voucher_id']);
                                if ($voucher) {
                                    $allocated = min($remainingAmount, (float)$voucher->balance);
                                    $voucher->paid_amount += $allocated;
                                    $voucher->balance -= $allocated;
                                    $voucher->status = $voucher->balance <= 0 ? 'paid' : 'partially_paid';
                                    $voucher->save();

                                    PaymentAllocation::create([
                                        'payment_id' => $payment->id,
                                        'student_voucher_id' => $voucher->id,
                                        'amount' => $allocated,
                                    ]);
                                    $remainingAmount -= $allocated;
                                }
                            } else {
                                // Default Allocation: Oldest outstanding first
                                $vouchers = StudentVoucher::where('student_fee_account_id', $record->id)
                                    ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
                                    ->orderBy('due_date', 'asc')
                                    ->orderBy('sequence_no', 'asc')
                                    ->get();

                                foreach ($vouchers as $voucher) {
                                    if ($remainingAmount <= 0) break;

                                    $allocated = min($remainingAmount, (float)$voucher->balance);
                                    $voucher->paid_amount += $allocated;
                                    $voucher->balance -= $allocated;
                                    $voucher->status = $voucher->balance <= 0 ? 'paid' : 'partially_paid';
                                    $voucher->save();

                                    PaymentAllocation::create([
                                        'payment_id' => $payment->id,
                                        'student_voucher_id' => $voucher->id,
                                        'amount' => $allocated,
                                    ]);

                                    $remainingAmount -= $allocated;
                                }
                            }

                            // If there is any leftover amount, it is stored as credit (advance payment)
                            // We can deduct it directly from future upcoming vouchers
                            if ($remainingAmount > 0) {
                                $upcomingVouchers = StudentVoucher::where('student_fee_account_id', $record->id)
                                    ->where('status', 'upcoming')
                                    ->orderBy('due_date', 'asc')
                                    ->get();

                                foreach ($upcomingVouchers as $voucher) {
                                    if ($remainingAmount <= 0) break;

                                    $allocated = min($remainingAmount, (float)$voucher->balance);
                                    $voucher->paid_amount += $allocated;
                                    $voucher->balance -= $allocated;
                                    $voucher->status = $voucher->balance <= 0 ? 'paid' : 'partially_paid';
                                    $voucher->save();

                                    PaymentAllocation::create([
                                        'payment_id' => $payment->id,
                                        'student_voucher_id' => $voucher->id,
                                        'amount' => $allocated,
                                    ]);

                                    $remainingAmount -= $allocated;
                                }
                            }

                            // Update StudentFeeAccount balances
                            $record->amount_paid += $amount;
                            $record->balance = max(0, $record->net_payable - $record->amount_paid);
                            $record->status = $record->balance <= 0 ? 'paid' : 'active';
                            $record->save();

                            // Audit Log
                            RebuildAuditLog::create([
                                'user_id' => filament()->auth()->id(),
                                'action' => 'fee_collection',
                                'description' => "Collected payment of PKR {$amount} (Receipt: {$receiptNumber}) for student {$record->student->full_name} ({$record->student->enrollment_number}).",
                                'ip_address' => request()->ip(),
                            ]);
                        });

                        Notification::make()
                            ->title('Payment Collected')
                            ->body('Payment successfully received and allocated to vouchers.')
                            ->success()
                            ->send();
                    })
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if (!filament()->auth()->user()->hasRole('Super Admin')) {
            $query->whereHas('student', function ($q) {
                $q->where('campus_id', filament()->auth()->user()->campus_id);
            });
        }
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeCollections::route('/'),
            'view' => Pages\ViewStudentFeeAccount::route('/{record}/account'),
        ];
    }
}

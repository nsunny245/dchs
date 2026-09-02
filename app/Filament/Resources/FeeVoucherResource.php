<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeeVoucherResource\Pages;
use App\Models\FeeHead;
use App\Models\FeeVoucher;
use App\Models\Student;
use App\Services\Fees\FeeVoucherService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class FeeVoucherResource extends Resource
{
    protected static ?string $model = FeeVoucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Fee Vouchers';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('custom_voucher_intro')
                    ->label('')
                    ->content(new HtmlString(
                        '<div class="rounded-2xl border border-amber-200 bg-gradient-to-r from-[#06192e] to-[#104b79] p-5 text-white shadow-sm">'
                        .'<div class="text-xs font-bold uppercase tracking-widest text-amber-300">Additional fee voucher</div>'
                        .'<div class="mt-1 text-lg font-black">Create a clear, student-specific payment request</div>'
                        .'<p class="mt-2 text-sm text-blue-100">For a partial payment against an existing admission installment, use <strong>Collect Custom / Partial Amount</strong> on the student fee account. Use this form only for a separate additional charge.</p>'
                        .'</div>'
                    ))
                    ->columnSpanFull(),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Voucher Information')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Voucher Title')
                                    ->placeholder('e.g. Replacement ID Card Fee')
                                    ->default('Custom Fee Voucher')
                                    ->required()
                                    ->maxLength(150)
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('student_id')
                                    ->relationship('student', 'full_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->full_name} ({$record->enrollment_number})")
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                                        if ($state) {
                                            $student = Student::find($state);
                                            if ($student) {
                                                $set('campus_id', $student->campus_id);
                                                $set('course_id', $student->course_id);
                                                $set('student_fee_account_id', $student->feeAccount?->id);
                                                if ($student->admission) {
                                                    $set('admission_id', $student->admission_id);
                                                    $set('academic_session_id', $student->admission->academic_session_id);
                                                }
                                            }
                                        }
                                    }),
                                Forms\Components\Hidden::make('student_fee_account_id'),
                                Forms\Components\Select::make('voucher_type')
                                    ->options([
                                        'new_enrollment' => 'New Enrollment',
                                        'monthly_installment' => 'Monthly Installment',
                                        'examination_fee' => 'Examination Fee',
                                        'verification_fee' => 'Verification Fee',
                                        'miscellaneous_fee' => 'Miscellaneous Fee',
                                        'other_fee' => 'Other Fee Head',
                                    ])
                                    ->default('monthly_installment')
                                    ->required()
                                    ->live(),
                                Forms\Components\Select::make('campus_id')
                                    ->relationship('campus', 'name')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('course_id')
                                    ->relationship('course', 'name')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('academic_session_id')
                                    ->relationship('academicSession', 'name')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('admission_id')
                                    ->relationship('admission', 'applicant_name')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\DatePicker::make('issue_date')
                                    ->default(now())
                                    ->required(),
                                Forms\Components\DatePicker::make('due_date')
                                    ->default(now()->addDays(10))
                                    ->required(),
                                Forms\Components\Select::make('orientation')
                                    ->options([
                                        'horizontal_three_part' => 'Horizontal 3-Part (A4 Portrait)',
                                        'portrait_three_part' => 'Portrait 3-Part (A4 Landscape)',
                                    ])
                                    ->default('horizontal_three_part')
                                    ->required(),
                                Forms\Components\Textarea::make('notes')
                                    ->columnSpanFull()
                                    ->placeholder('Add additional instructions or comments...'),
                            ])->columns(2)->columnSpan(2)
                            ->extraAttributes(['class' => 'rounded-2xl']),

                        Forms\Components\Card::make()
                            ->schema([
                                Forms\Components\Placeholder::make('summary_heading')
                                    ->label('')
                                    ->content(new HtmlString('<strong class="text-lg">Calculated Summary</strong>')),
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->default(0.00)
                                    ->readOnly(),
                                Forms\Components\TextInput::make('discount_amount')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->default(0.00)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::updateTotal($set, $get)),
                                Forms\Components\TextInput::make('scholarship_amount')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->default(0.00)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::updateTotal($set, $get)),
                                Forms\Components\TextInput::make('previous_balance')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->default(0.00)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::updateTotal($set, $get)),
                                Forms\Components\TextInput::make('late_fee_amount')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->default(0.00)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::updateTotal($set, $get)),
                                Forms\Components\TextInput::make('fine_amount')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->default(0.00)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::updateTotal($set, $get)),
                                Forms\Components\Placeholder::make('total_display')
                                    ->label('Total Payable (PKR)')
                                    ->content(fn ($get) => 'PKR '.number_format($get('total_amount'), 2)),
                                Forms\Components\Hidden::make('total_amount')
                                    ->default(0.00),
                                Forms\Components\Hidden::make('balance_amount')
                                    ->default(0.00),
                            ])->columnSpan(1)
                            ->extraAttributes(['class' => 'rounded-2xl border-t-4 border-t-amber-400']),
                    ]),

                Forms\Components\Section::make('Fee Voucher Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->defaultItems(0)
                            ->schema([
                                Forms\Components\Select::make('fee_head_id')
                                    ->label('Fee Head')
                                    ->options(fn (Forms\Get $get) => FeeHead::query()
                                        ->where('is_active', true)
                                        ->where(fn (Builder $query) => $query
                                            ->whereNull('course_id')
                                            ->orWhere('course_id', $get('../../course_id')))
                                        ->orderBy('sort_order')
                                        ->pluck('name', 'id'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $head = FeeHead::find($state);
                                            if ($head) {
                                                $set('description', $head->name);
                                                $set('unit_amount', $head->default_amount ?? 0.00);
                                                $set('amount', $head->default_amount ?? 0.00);
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('description')
                                    ->required(),
                                Forms\Components\Hidden::make('quantity')->default(1),
                                Forms\Components\Hidden::make('unit_amount')->default(0),
                                Forms\Components\TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('unit_amount', $state ?: 0)),
                                Forms\Components\Select::make('adjustment_type')
                                    ->options([
                                        'debit' => 'Debit (Addition)',
                                        'credit' => 'Credit (Subtraction)',
                                        'discount' => 'Discount',
                                    ])
                                    ->default('debit')
                                    ->required(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                // Calculate subtotal based on items
                                $items = $get('items') ?? [];
                                $sub = 0.00;
                                foreach ($items as $item) {
                                    $sub += (float) ($item['amount'] ?? 0.00);
                                }
                                $set('subtotal', $sub);
                                self::updateTotal($set, $get);
                            }),
                    ])->columnSpanFull()
                    ->extraAttributes(['class' => 'rounded-2xl']),
            ]);
    }

    public static function updateTotal(Forms\Set $set, Forms\Get $get): void
    {
        $sub = (float) $get('subtotal');
        $prev = (float) $get('previous_balance');
        $late = (float) $get('late_fee_amount');
        $fine = (float) $get('fine_amount');
        $disc = (float) $get('discount_amount');
        $schol = (float) $get('scholarship_amount');

        $total = ($sub + $prev + $late + $fine) - ($disc + $schol);
        if ($total < 0) {
            $total = 0.00;
        }

        $set('total_amount', $total);
        $set('balance_amount', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\TextColumn::make('voucher_number')
                    ->searchable()
                    ->sortable()
                    ->label('Voucher No'),
                Tables\Columns\TextColumn::make('student.full_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.enrollment_number')
                    ->searchable()
                    ->label('Student ID'),
                Tables\Columns\TextColumn::make('campus.name')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('course.name')
                    ->placeholder('N/A')
                    ->limit(20),
                Tables\Columns\TextColumn::make('voucher_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('issue_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('PKR')
                    ->label('Total'),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('PKR')
                    ->label('Paid'),
                Tables\Columns\TextColumn::make('balance_amount')
                    ->money('PKR')
                    ->label('Balance'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'info' => 'issued',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'secondary' => ['cancelled', 'void'],
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus_id')
                    ->relationship('campus', 'name')
                    ->label('Campus'),
                Tables\Filters\SelectFilter::make('course_id')
                    ->relationship('course', 'name')
                    ->label('Course'),
                Tables\Filters\SelectFilter::make('voucher_type')
                    ->options([
                        'new_enrollment' => 'New Enrollment',
                        'monthly_installment' => 'Monthly Installment',
                        'examination_fee' => 'Examination Fee',
                        'verification_fee' => 'Verification Fee',
                        'miscellaneous_fee' => 'Miscellaneous Fee',
                        'other_fee' => 'Other Fee Head',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'issued' => 'Issued',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                        'void' => 'Void',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('issue')
                        ->label('Issue Voucher')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === 'draft')
                        ->action(function ($record) {
                            try {
                                FeeVoucherService::issueVoucher($record);
                                Notification::make()->title('Voucher Issued')->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Action Failed')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Tables\Actions\Action::make('recordPayment')
                        ->label('Record Payment')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn ($record) => in_array($record->status, ['issued', 'partially_paid', 'overdue']))
                        ->form([
                            Forms\Components\TextInput::make('amount')
                                ->label('Amount (PKR)')
                                ->numeric()
                                ->prefix('PKR')
                                ->required()
                                ->maxValue(fn ($record) => $record->balance_amount)
                                ->minValue(1),
                            Forms\Components\DatePicker::make('payment_date')
                                ->default(now())
                                ->required(),
                            Forms\Components\Select::make('payment_method')
                                ->options([
                                    'cash' => 'Cash',
                                    'bank_deposit' => 'Bank Deposit',
                                    'bank_transfer' => 'Bank Transfer',
                                    'cheque' => 'Cheque',
                                    'online' => 'Online Account',
                                ])
                                ->default('cash')
                                ->required(),
                            Forms\Components\TextInput::make('transaction_reference')
                                ->label('Ref No.'),
                            Forms\Components\TextInput::make('bank_name')
                                ->label('Bank Name'),
                            Forms\Components\Textarea::make('notes'),
                        ])
                        ->action(function ($record, array $data) {
                            try {
                                FeeVoucherService::recordPayment($record, $data);
                                Notification::make()->title('Payment Recorded')->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Action Failed')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Tables\Actions\Action::make('printHorizontal')
                        ->label('Print Horizontal (A4 Portrait)')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn ($record) => route('fee-vouchers.print.horizontal', $record->id))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('printPortrait')
                        ->label('Print Columns (A4 Landscape)')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn ($record) => route('fee-vouchers.print.portrait', $record->id))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('cancel')
                        ->label('Cancel Voucher')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->required()
                                ->placeholder('Reason for cancellation...'),
                        ])
                        ->visible(fn ($record) => $record->status !== 'paid' && $record->status !== 'cancelled')
                        ->action(function ($record, array $data) {
                            try {
                                FeeVoucherService::cancelVoucher($record, $data['reason']);
                                Notification::make()->title('Voucher Cancelled')->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Action Failed')->body($e->getMessage())->danger()->send();
                            }
                        }),
                ]),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeVouchers::route('/'),
            'create' => Pages\CreateFeeVoucher::route('/create'),
            'edit' => Pages\EditFeeVoucher::route('/{record}/edit'),
        ];
    }
}

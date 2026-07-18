<?php

namespace App\Filament\Franchisor\Resources;

use App\Filament\Franchisor\Resources\FranchisorAdmissionResource\Pages;
use App\Models\Admission;
use App\Models\Franchisor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FranchisorAdmissionResource extends Resource
{
    protected static ?string $model = Admission::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Student Admissions';
    protected static ?string $modelLabel = 'Student Admission';
    protected static ?string $pluralModelLabel = 'Student Admissions';

    public static function getFranchisorType(): ?string
    {
        $user = filament()->auth()->user();
        if (!$user) return null;

        $inboundUserId = \App\Models\Setting::where('key', 'franchisor_inbound_user_id')->value('value');
        $outboundUserId = \App\Models\Setting::where('key', 'franchisor_outbound_user_id')->value('value');

        if ($user->id == $inboundUserId || $user->hasRole('Franchisor Inbound')) {
            return 'inbound';
        }
        if ($user->id == $outboundUserId || $user->hasRole('Franchisor Outbound')) {
            return 'outbound';
        }
        return null;
    }

    public static function form(Form $form): Form
    {
        $isCreate = $form->getOperation() === 'create';

        return $form
            ->schema([
                Forms\Components\Section::make('Franchisor & Program Assignment')
                    ->schema([
                        Forms\Components\Select::make('franchisor_id')
                            ->label('Franchisor Institution')
                            ->options(function () {
                                $type = self::getFranchisorType();
                                if (!$type) {
                                    return Franchisor::where('is_active', true)->pluck('name', 'id');
                                }
                                return Franchisor::where('type', $type)->where('is_active', true)->pluck('name', 'id');
                            })
                            ->default(fn () => filament()->auth()->user()?->franchisor?->id)
                            ->disabled(fn () => filament()->auth()->user()?->franchisor !== null)
                            ->dehydrated()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('course_id')
                            ->label('Program / Course')
                            ->options(function (Forms\Get $get) {
                                $franchisorId = $get('franchisor_id') ?: (filament()->auth()->user()?->franchisor?->id ?? filament()->auth()->user()?->id);
                                
                                // Fallback: If user is connected as franchisor inbound/outbound user directly
                                if (!$franchisorId) {
                                    $user = filament()->auth()->user();
                                    $franchisor = \App\Models\Franchisor::where('user_id', $user?->id)->first();
                                    $franchisorId = $franchisor?->id;
                                }

                                if (!$franchisorId) {
                                    return \App\Models\Course::pluck('name', 'id');
                                }

                                return \App\Models\Course::whereIn('id', function ($query) use ($franchisorId) {
                                    $query->select('course_id')
                                        ->from('franchisor_course_deals')
                                        ->where('franchisor_id', $franchisorId);
                                })->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                if (!$state) return;
                                $franchisorId = $get('franchisor_id') ?: filament()->auth()->user()?->franchisor?->id;
                                if (!$franchisorId) return;

                                $deal = \App\Models\FranchisorCourseDeal::where('franchisor_id', $franchisorId)
                                    ->where('course_id', $state)
                                    ->first();

                                $cost = $deal ? (float) $deal->per_seat_cost : 250000.00;
                                $set('seat_cost', $cost);
                                
                                $half = round($cost / 2, 2);
                                $set('installments', [
                                    [
                                        'title' => '50% Advance Seat Payment',
                                        'amount' => $half,
                                    ],
                                    [
                                        'title' => '50% Roll Number Slip Issue',
                                        'amount' => $half,
                                    ]
                                ]);
                            }),
                        Forms\Components\Select::make('campus_id')
                            ->relationship('campus', 'name')
                            ->label('Campus Location')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('academic_session_id')
                            ->relationship('academicSession', 'name')
                            ->label('Academic Session')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Student Profile')
                    ->schema([
                        Forms\Components\TextInput::make('applicant_name')
                            ->label('Applicant Full Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('father_name')
                            ->label('Father Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cnic')
                            ->label('Student CNIC / B-Form')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('dob')
                            ->label('Date of Birth')
                            ->required(),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Seat Pricing & Installments Plan')
                    ->visible($isCreate)
                    ->schema([
                        Forms\Components\TextInput::make('seat_cost')
                            ->label('Total Seat Fee Price')
                            ->numeric()
                            ->prefix('PKR')
                            ->required($isCreate)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $half = round((float) $state / 2, 2);
                                $set('installments', [
                                    [
                                        'title' => '50% Advance Seat Payment',
                                        'amount' => $half,
                                    ],
                                    [
                                        'title' => '50% Roll Number Slip Issue',
                                        'amount' => $half,
                                    ]
                                ]);
                            }),
                        Forms\Components\Repeater::make('installments')
                            ->label('Installment Schedules')
                            ->schema([
                                Forms\Components\TextInput::make('title')->required(),
                                Forms\Components\TextInput::make('amount')->numeric()->prefix('PKR')->required(),
                                Forms\Components\DatePicker::make('due_date'),
                            ])
                            ->defaultItems(2)
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\TextColumn::make('applicant_name')->label('Student Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('franchisor.name')->label('Institution')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('course.name')->label('Program')->sortable(),
                Tables\Columns\TextColumn::make('campus.city')->label('Campus'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')
                    ->relationship('course', 'name')
                    ->label('Program'),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = filament()->auth()->user();

        if ($user && $user->franchisor) {
            $query->where('franchisor_id', $user->franchisor->id);
        } else {
            $type = self::getFranchisorType();
            if ($type) {
                $query->whereHas('franchisor', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            }
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFranchisorAdmissions::route('/'),
            'create' => Pages\CreateFranchisorAdmission::route('/create'),
            'edit' => Pages\EditFranchisorAdmission::route('/{record}/edit'),
        ];
    }
}

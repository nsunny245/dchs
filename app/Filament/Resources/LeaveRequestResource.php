<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        $user = filament()->auth()->user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');

        return $form
            ->schema([
                Forms\Components\Section::make('Leave Request Details')
                    ->schema([
                        Forms\Components\Select::make('campus_id')
                            ->relationship('campus', 'name')
                            ->required()
                            ->default(fn () => $user?->campus_id)
                            ->disabled(!$isSuperAdmin)
                            ->dehydrated(),
                        Forms\Components\Select::make('staff_id')
                            ->label('Teacher / Staff Member')
                            ->options(function (Forms\Get $get) use ($user) {
                                $campusId = $get('campus_id') ?? $user?->campus_id;
                                $q = Staff::query();
                                if ($campusId) {
                                    $q->where('campus_id', $campusId);
                                }
                                return $q->pluck('full_name', 'id');
                            })
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('leave_type')
                            ->options([
                                'Casual Leave' => 'Casual Leave',
                                'Sick Leave' => 'Sick Leave',
                                'Annual Leave' => 'Annual Leave',
                                'Emergency Leave' => 'Emergency Leave',
                                'Maternity Leave' => 'Maternity Leave',
                                'Unpaid Leave' => 'Unpaid Leave',
                                'Official Duty' => 'Official Duty',
                                'Study Leave' => 'Study Leave',
                            ])
                            ->required(),
                        Forms\Components\Select::make('day_type')
                            ->options([
                                'full_day' => 'Full Day',
                                'half_day' => 'Half Day',
                            ])
                            ->default('full_day')
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->required(),
                        Forms\Components\TextInput::make('requested_days')
                            ->numeric()
                            ->required()
                            ->default(1),
                        Forms\Components\Select::make('payroll_impact')
                            ->options([
                                'paid' => 'Paid Leave',
                                'unpaid' => 'Unpaid Leave',
                            ])
                            ->default('paid')
                            ->required(),
                        Forms\Components\Textarea::make('reason')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('Supporting Document / Medical Certificate')
                            ->directory('leave/evidence')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('campus_note')
                            ->label('Internal Campus Note')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = filament()->auth()->user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.full_name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('staff.employee_id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('campus.name')
                    ->label('Campus')
                    ->hidden(fn () => filament()->getCurrentPanel()?->getId() === 'campus'),
                Tables\Columns\TextColumn::make('leave_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date(),
                Tables\Columns\TextColumn::make('requested_days')
                    ->numeric(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        'returned' => 'info',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible($isSuperAdmin)
                    ->action(function (LeaveRequest $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_days' => $record->requested_days,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->title('Leave Approved')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible($isSuperAdmin)
                    ->form([
                        Forms\Components\Textarea::make('decision_note')->label('Reason for Rejection')->required(),
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'decision_note' => $data['decision_note'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->title('Leave Rejected')->danger()->send();
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = filament()->auth()->user();

        if ($user && $user->campus_id !== null) {
            $query->where('campus_id', $user->campus_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
        ];
    }
}

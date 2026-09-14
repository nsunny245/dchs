<?php

namespace App\Filament\Resources\AdmissionResource\Pages;

use App\Filament\Resources\AdmissionResource;
use App\Models\AcademicSession;
use App\Models\Campus;
use App\Models\Course;
use App\Models\StudentImportBatch;
use App\Services\Admissions\BulkAdmissionService;
use App\Services\Fees\OfficialFeeStructureResolver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BulkAdmissions extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = AdmissionResource::class;

    protected static string $view = 'filament.resources.admission-resource.pages.bulk-admissions';

    protected static ?string $title = 'Manual Bulk Admission Workbench';

    public ?array $data = [];

    public ?int $lastBatchId = null;

    public array $lastResults = [];

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function mount(): void
    {
        $user = filament()->auth()->user();
        $campusId = $user?->campus_id ?: Campus::query()->where('is_active', true)->value('id');
        $sessionId = $campusId
            ? AcademicSession::query()->where('campus_id', $campusId)->where('is_active', true)->latest('start_date')->value('id')
            : null;

        $this->form->fill([
            'campus_id' => $campusId,
            'default_academic_session_id' => $sessionId,
            'default_course_id' => null,
            'default_previous_education' => null,
            'default_shift' => 'morning',
            'default_admission_date' => now()->toDateString(),
            'default_payment_date' => now()->toDateString(),
            'default_payment_method' => 'cash',
            'students' => [$this->blankStudent()],
        ]);
    }

    public function form(Form $form): Form
    {
        $user = filament()->auth()->user();
        $isSuperAdmin = $user?->hasRole('Super Admin') ?? false;

        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Common defaults')
                    ->description('Set shared values once, then apply them to every student card. You can still change any student individually.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Forms\Components\Select::make('campus_id')
                            ->label('Campus')
                            ->options(fn () => Campus::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->disabled(! $isSuperAdmin)
                            ->dehydrated()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('default_academic_session_id', null)),
                        Forms\Components\Select::make('default_academic_session_id')
                            ->label('Session')
                            ->options(fn (Forms\Get $get) => AcademicSession::query()
                                ->where('campus_id', $get('campus_id'))
                                ->where('is_active', true)
                                ->latest('start_date')
                                ->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false),
                        Forms\Components\Select::make('default_course_id')
                            ->label('Program')
                            ->options(fn () => Course::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->live(),
                        Forms\Components\TextInput::make('default_previous_education')
                            ->label('Qualification')
                            ->placeholder('e.g. Matric with Biology')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('default_shift')
                            ->label('Shift')
                            ->options(['morning' => 'Morning', 'evening' => 'Evening', 'weekend' => 'Weekend', 'offline' => 'Offline / Exams'])
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('default_admission_date')
                            ->label('Admission date')
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('default_payment_date')
                            ->label('Opening payment date')
                            ->native(false),
                        Forms\Components\Select::make('default_payment_method')
                            ->label('Opening payment method')
                            ->options($this->paymentMethods())
                            ->native(false),
                        Forms\Components\Placeholder::make('default_fee_plan_preview')
                            ->label('Matched official fee plan')
                            ->content(function (Forms\Get $get): \Illuminate\Support\HtmlString {
                                $structure = $this->resolvePreviewStructure(
                                    (int) ($get('default_course_id') ?? 0),
                                    (int) ($get('campus_id') ?? 0),
                                    (int) ($get('default_academic_session_id') ?? 0),
                                    $get('default_admission_date'),
                                );

                                if ($structure) {
                                    $name = e($structure->name ?: 'Official fee plan');
                                    $fee = number_format((float) $structure->total_fee, 2);
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='flex items-center gap-2.5 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700/60 rounded-xl text-emerald-800 dark:text-emerald-300 text-sm font-semibold mt-1 shadow-sm'>
                                            <svg class='w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>
                                            <span><strong>{$name}</strong> — PKR {$fee}</span>
                                        </div>
                                    ");
                                }

                                return new \Illuminate\Support\HtmlString("
                                    <div class='flex items-center gap-2.5 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 text-sm font-medium mt-1'>
                                        <svg class='w-5 h-5 text-slate-400 flex-shrink-0' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>
                                        <span>Select campus, session, and program to auto-match fee plan.</span>
                                    </div>
                                ");
                            }),
                    ])
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 4])
                    ->extraAttributes(['class' => 'bulk-defaults']),

                Forms\Components\Repeater::make('students')
                    ->label('Student entries')
                    ->schema([
                        Forms\Components\Section::make('Student identity')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\TextInput::make('applicant_name')->label('Student name')->required()->maxLength(255),
                                Forms\Components\TextInput::make('father_name')->label('Father / guardian name')->required()->maxLength(255),
                                Forms\Components\DatePicker::make('dob')->label('Date of birth')->required()->maxDate(now()->subDay())->native(false),
                                Forms\Components\Select::make('gender')->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])->required()->native(false),
                                Forms\Components\TextInput::make('phone')->label('Student phone')->tel()->required()->maxLength(30),
                                Forms\Components\TextInput::make('cnic')->label('CNIC / B-Form')->placeholder('35202-1234567-1')->required()->maxLength(20),
                                Forms\Components\Textarea::make('address')->label('Address')->required()->rows(2)->columnSpanFull(),
                            ])->columns(['default' => 1, 'md' => 2, 'xl' => 3])->compact(),

                        Forms\Components\Section::make('Academic placement')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\TextInput::make('previous_education')->label('Qualification')->required()->maxLength(255),
                                Forms\Components\Select::make('academic_session_id')
                                    ->label('Session')
                                    ->options(fn (Forms\Get $get) => AcademicSession::query()
                                        ->where('campus_id', $get('../../campus_id'))
                                        ->where('is_active', true)
                                        ->latest('start_date')->pluck('name', 'id'))
                                    ->required()->searchable()->native(false)->live()
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => $this->refreshFeePreview($get, $set)),
                                Forms\Components\Select::make('course_id')
                                    ->label('Program')
                                    ->options(fn () => Course::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                                    ->required()->searchable()->native(false)->live()
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => $this->refreshFeePreview($get, $set)),
                                Forms\Components\Select::make('shift')->options(['morning' => 'Morning', 'evening' => 'Evening', 'weekend' => 'Weekend', 'offline' => 'Offline / Exams'])->required()->native(false),
                                Forms\Components\DatePicker::make('admission_date')->required()->native(false),
                                Forms\Components\TextInput::make('existing_enrollment_number')
                                    ->label('Existing enrollment number (optional)')
                                    ->helperText('Leave blank to generate the next campus/program enrollment number.'),
                            ])->columns(['default' => 1, 'md' => 2, 'xl' => 3])->compact(),

                        Forms\Components\Section::make('Opening fee position')
                            ->description('No installment vouchers are generated now. The schedule will be created when the full profile is completed later.')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\TextInput::make('fee_plan_name')
                                    ->label('Matched fee plan')
                                    ->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('official_tuition')
                                    ->label('Official tuition / fee plan')
                                    ->prefix('PKR')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('concession_amount')
                                    ->label('Concession')->prefix('PKR')->numeric()->minValue(0)->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => $this->refreshFeePreview($get, $set)),
                                Forms\Components\TextInput::make('net_tuition')
                                    ->label('Net tuition')->prefix('PKR')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('amount_paid')
                                    ->label('Amount already paid')->prefix('PKR')->numeric()->minValue(0)->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => $this->refreshFeePreview($get, $set)),
                                Forms\Components\TextInput::make('outstanding')
                                    ->label('Outstanding balance')->prefix('PKR')->disabled()->dehydrated(false),
                                Forms\Components\DatePicker::make('payment_date')->label('Payment date')->native(false),
                                Forms\Components\Select::make('payment_method')->label('Payment method')->options($this->paymentMethods())->native(false),
                                Forms\Components\TextInput::make('transaction_reference')->label('Reference / receipt note')->maxLength(255),
                                Forms\Components\Textarea::make('concession_reason')->label('Concession note')->rows(2),
                            ])->columns(['default' => 1, 'md' => 2, 'xl' => 3])->compact(),
                    ])
                    ->itemLabel(fn (array $state): ?string => filled($state['applicant_name'] ?? null)
                        ? ($state['applicant_name'].' — '.($state['phone'] ?? 'phone pending'))
                        : 'New student entry')
                    ->defaultItems(1)
                    ->addActionLabel('Add another student')
                    ->reorderable(false)
                    ->collapsible()
                    ->cloneable()
                    ->minItems(1)
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'bulk-student-repeater']),
            ]);
    }

    public function applyDefaults(): void
    {
        $state = $this->form->getRawState();
        $students = collect($state['students'] ?? [])->map(function (array $student) use ($state): array {
            $student['academic_session_id'] = $state['default_academic_session_id'] ?? null;
            $student['course_id'] = $state['default_course_id'] ?? null;
            $student['previous_education'] = $state['default_previous_education'] ?? null;
            $student['shift'] = $state['default_shift'] ?? 'morning';
            $student['admission_date'] = $state['default_admission_date'] ?? now()->toDateString();
            $student['payment_date'] = $state['default_payment_date'] ?? null;
            $student['payment_method'] = $state['default_payment_method'] ?? 'cash';

            return $this->withFeePreview($student, (int) ($state['campus_id'] ?? 0));
        })->all();
        $state['students'] = $students;
        $this->form->fill($state);

        Notification::make()->success()->title('Defaults applied to all student cards')->send();
    }

    public function import(): void
    {
        $state = $this->form->getState();
        $defaults = [
            'campus_id' => $state['campus_id'],
            'academic_session_id' => $state['default_academic_session_id'],
            'course_id' => $state['default_course_id'],
            'previous_education' => $state['default_previous_education'],
            'shift' => $state['default_shift'],
            'admission_date' => $state['default_admission_date'],
            'payment_date' => $state['default_payment_date'],
            'payment_method' => $state['default_payment_method'],
        ];
        $batch = app(BulkAdmissionService::class)->import($state['students'], filament()->auth()->user(), $defaults);
        $this->lastBatchId = $batch->id;
        $this->lastResults = $batch->rows->map(fn ($row): array => [
            'row' => $row->row_number,
            'name' => $row->raw_data['applicant_name'] ?? 'Unnamed student',
            'status' => $row->status,
            'enrollment' => $row->normalized_data['enrollment_number'] ?? null,
            'errors' => $row->errors ?? [],
        ])->all();

        if ($batch->failed_rows > 0) {
            $failed = $batch->rows->where('status', 'failed')->pluck('raw_data')->values()->all();
            $state['students'] = $failed ?: [$this->blankStudent()];
            $this->form->fill($state);
            Notification::make()->warning()->persistent()
                ->title("{$batch->successful_rows} imported; {$batch->failed_rows} need attention")
                ->body('Successful rows are safe. Only failed rows remain in the workbench for correction.')
                ->send();
        } else {
            $state['students'] = [$this->blankStudent()];
            $this->form->fill($state);
            Notification::make()->success()
                ->title("{$batch->successful_rows} students imported")
                ->body('Opening balances were recorded. Installment vouchers will be generated later when each full profile is completed.')
                ->send();
        }
    }

    private function refreshFeePreview(Forms\Get $get, Forms\Set $set): void
    {
        $campusId = (int) ($get('../../campus_id') ?? 0);
        $row = [
            'course_id' => $get('course_id'),
            'academic_session_id' => $get('academic_session_id'),
            'admission_date' => $get('admission_date'),
            'concession_amount' => $get('concession_amount'),
            'amount_paid' => $get('amount_paid'),
        ];
        $preview = $this->withFeePreview($row, $campusId);
        foreach (['fee_plan_name', 'official_tuition', 'net_tuition', 'outstanding'] as $field) {
            $set($field, $preview[$field] ?? null);
        }
    }

    private function withFeePreview(array $row, int $campusId): array
    {
        $courseId = (int) ($row['course_id'] ?? 0);
        $sessionId = (int) ($row['academic_session_id'] ?? 0);
        $structure = $this->resolvePreviewStructure($courseId, $campusId, $sessionId, $row['admission_date'] ?? now());
        $tuition = round((float) ($structure?->total_fee ?? 0), 2);
        $concession = min(max(0, (float) ($row['concession_amount'] ?? 0)), $tuition);
        $paid = max(0, (float) ($row['amount_paid'] ?? 0));
        $row['fee_plan_name'] = $structure?->name ?: ($structure ? 'Official fee plan' : 'No matching fee plan');
        $row['official_tuition'] = number_format($tuition, 2, '.', '');
        $row['net_tuition'] = number_format(max(0, $tuition - $concession), 2, '.', '');
        $row['outstanding'] = number_format(max(0, $tuition - $concession - $paid), 2, '.', '');

        return $row;
    }

    private function resolvePreviewStructure(int $courseId, int $campusId, int $sessionId, mixed $date): ?\App\Models\FeeStructure
    {
        if (! $courseId || ! $campusId || ! $sessionId) {
            return null;
        }

        return app(OfficialFeeStructureResolver::class)->resolve(
            $courseId,
            $campusId,
            $sessionId,
            filled($date) ? Carbon::parse($date) : now(),
        );
    }

    private function blankStudent(): array
    {
        return [
            'gender' => 'male',
            'concession_amount' => 0,
            'amount_paid' => 0,
            'payment_method' => 'cash',
        ];
    }

    private function paymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank transfer',
            'cheque' => 'Cheque',
            'card' => 'Card',
            'other' => 'Other',
        ];
    }
}

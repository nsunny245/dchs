<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdmissionResource\Pages;
use App\Models\AcademicSession;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeHead;
use App\Services\EnrollmentService;
use App\Services\Fees\InstallmentPlanGenerator;
use App\Services\Fees\OfficialFeeStructureResolver;
use App\Services\Fees\VoucherGenerationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class AdmissionResource extends Resource
{
    protected static ?string $model = Admission::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Student Relations';

    protected static ?int $navigationSort = 1;

    protected static function getSidebarPlaceholder(int $stepIndex, int $percentage): Forms\Components\Placeholder
    {
        return Forms\Components\Placeholder::make("admission_sidebar_step_{$stepIndex}")
            ->label('')
            ->content(function (Forms\Get $get) use ($stepIndex, $percentage) {
                $courseId = $get('course_id');
                $campusId = $get('campus_id');
                $sessionId = $get('academic_session_id');

                return view('filament.admissions.components.context-panel', [
                    'stepIndex' => $stepIndex,
                    'percentage' => $percentage,
                    'studentName' => $get('applicant_name') ?: 'Not entered yet',
                    'course' => $courseId ? (Course::find($courseId)?->name ?: 'Not selected yet') : 'Not selected yet',
                    'campus' => $campusId ? (Campus::find($campusId)?->name ?: 'Not selected yet') : 'Not selected yet',
                    'session' => $sessionId ? (AcademicSession::find($sessionId)?->name ?: 'Not selected yet') : 'Not selected yet',
                    'shift' => filled($get('shift')) ? ucfirst((string) $get('shift')) : 'Not selected yet',
                ]);
            })
            ->extraAttributes(['class' => 'admission-context-placeholder']);
    }

    protected static function getStepIntroPlaceholder(int $stepIndex): Forms\Components\Placeholder
    {
        $steps = [
            1 => ['Student Photo', 'Upload a clear passport-size photograph of the applicant.', 'heroicon-o-camera'],
            2 => ['Student Information', "Enter the applicant's personal and contact details.", 'heroicon-o-user'],
            3 => ['Parent or Guardian', "Add the applicant's parent, guardian, and emergency contact details.", 'heroicon-o-users'],
            4 => ['Academic Details', "Record the applicant's previous qualifications and results.", 'heroicon-o-academic-cap'],
            5 => ['Documents Vault', "Upload and verify the applicant's required documents.", 'heroicon-o-folder-open'],
            6 => ['Course & Fee Plan', 'Assign course details and configure the student fee plan.', 'heroicon-o-banknotes'],
            7 => ['Review & Confirm', 'Review all details before submitting the admission.', 'heroicon-o-shield-check'],
        ];

        [$title, $description, $icon] = $steps[$stepIndex];

        return Forms\Components\Placeholder::make("admission_step_intro_{$stepIndex}")
            ->label('')
            ->content(view('filament.admissions.components.step-intro', compact('title', 'description', 'icon')))
            ->columnSpanFull()
            ->extraAttributes(['class' => 'admission-step-intro-placeholder']);
    }

    protected static function getDocumentCard(
        string $fileField,
        string $statusField,
        string $label,
        string $statusLabel,
        string $placeholder,
    ): Forms\Components\Group {
        return Forms\Components\Group::make([
            Forms\Components\FileUpload::make($fileField)
                ->label($label)
                ->directory('student-docs')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->maxSize(5120)
                ->downloadable()
                ->openable()
                ->placeholder($placeholder)
                ->columnSpan(3),
            Forms\Components\Select::make($statusField)
                ->label($statusLabel)
                ->options([
                    'missing' => 'Missing',
                    'uploaded' => 'Uploaded',
                    'pending' => 'Pending',
                    'under_review' => 'Under Review',
                    'not_required' => 'Not Required',
                    'verified' => 'Verified',
                    'rejected' => 'Rejected',
                ])
                ->default('pending')
                ->columnSpan(1),
        ])
            ->columns(4)
            ->extraAttributes(['class' => 'admission-document-card'])
            ->columnSpan(1);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('missing_docs_notice')
                    ->label('⚠️ Document Status')
                    ->content('Notice: Some required documents (CNIC copy, Matric certificate copy, or Domicile copy) are missing for this applicant. Please upload them to complete the record.')
                    ->visible(fn ($record) => $record && ($record->status === 'documents_pending' || empty($record->cnic_copy) || empty($record->matric_copy) || empty($record->domicile_copy)))
                    ->columnSpanFull(),

                Forms\Components\Wizard::make([
                    // Step 1: Student Photo
                    Forms\Components\Wizard\Step::make('Student Photo')
                        ->icon('heroicon-o-camera')
                        ->description('Upload the passport-size picture of the student')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(1),
                                        Forms\Components\FileUpload::make('student_photo')
                                            ->label('Student Profile Photo')
                                            ->directory('student-photos')
                                            ->image()
                                            ->imagePreviewHeight('320')
                                            ->panelAspectRatio('4:5')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios(['4:5'])
                                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                            ->maxSize(4096)
                                            ->helperText('Use a clear passport-style photograph with a white or light-blue background. JPG or PNG, maximum 4 MB.')
                                            ->columnSpanFull(),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-photo-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(1, 14),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 2: Student Information
                    Forms\Components\Wizard\Step::make('Student Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(2),
                                        Forms\Components\Section::make('Personal Identity')
                                            ->schema([
                                                Forms\Components\TextInput::make('applicant_name')
                                                    ->label('Student Full Name')
                                                    ->default('Draft Student')
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('cnic')
                                                    ->label('Student CNIC or B-Form #')
                                                    ->maxLength(255)
                                                    ->helperText('Format: 35202-1234567-1')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state) {
                                                        if (empty($state)) {
                                                            return;
                                                        }
                                                        if (Admission::where('cnic', $state)->exists()) {
                                                            Notification::make()
                                                                ->title('Warning: Possible Duplicate')
                                                                ->body("An admission application with CNIC/B-Form {$state} already exists.")
                                                                ->warning()
                                                                ->send();
                                                        }
                                                    }),
                                                Forms\Components\DatePicker::make('dob')
                                                    ->label('Date of Birth')
                                                    ->maxDate(now()),
                                                Forms\Components\Select::make('gender')
                                                    ->label('Gender')
                                                    ->options([
                                                        'male' => 'Male',
                                                        'female' => 'Female',
                                                        'other' => 'Other',
                                                    ]),
                                                Forms\Components\Select::make('blood_group')
                                                    ->label('Blood Group')
                                                    ->options([
                                                        'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-',
                                                        'O+' => 'O+', 'O-' => 'O-', 'AB+' => 'AB+', 'AB-' => 'AB-',
                                                    ]),
                                                Forms\Components\Select::make('workflow_metadata.nationality')
                                                    ->label('Nationality')
                                                    ->options(['Pakistani' => 'Pakistani'])
                                                    ->default('Pakistani'),
                                                Forms\Components\Select::make('workflow_metadata.religion')
                                                    ->label('Religion')
                                                    ->options([
                                                        'Islam' => 'Islam', 'Christianity' => 'Christianity',
                                                        'Hinduism' => 'Hinduism', 'Sikhism' => 'Sikhism', 'Other' => 'Other',
                                                    ])
                                                    ->default('Islam'),
                                                Forms\Components\TextInput::make('caste')
                                                    ->label('Caste (Optional)')
                                                    ->maxLength(255),
                                            ])->columns(4),

                                        Forms\Components\Section::make('Contact Details')
                                            ->schema([
                                                Forms\Components\TextInput::make('phone')
                                                    ->label('Mobile Number')
                                                    ->tel()
                                                    ->helperText('Format: 03001234567 or +923001234567')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state) {
                                                        if (empty($state)) {
                                                            return;
                                                        }
                                                        if (Admission::where('phone', $state)->exists()) {
                                                            Notification::make()
                                                                ->title('Warning: Possible Duplicate Mobile')
                                                                ->body("An admission application with mobile number {$state} already exists.")
                                                                ->warning()
                                                                ->send();
                                                        }
                                                    }),
                                                Forms\Components\TextInput::make('email')
                                                    ->label('Email (Optional)')
                                                    ->email()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('city')
                                                    ->label('City')
                                                    ->default('Okara'),
                                                Forms\Components\TextInput::make('domicile_district')
                                                    ->label('Domicile (District)')
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('address')
                                                    ->label('Current Address')
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ])->columns(4),

                                        Forms\Components\Section::make('Admission Preferences')
                                            ->schema([
                                                Forms\Components\Select::make('shift')
                                                    ->label('Shift Preference')
                                                    ->options([
                                                        'morning' => 'Morning',
                                                        'evening' => 'Evening',
                                                    ])
                                                    ->default('morning'),
                                                Forms\Components\Select::make('campus_id')
                                                    ->relationship('campus', 'name')
                                                    ->label('Preferred Campus')
                                                    ->default(fn () => filament()->auth()->user()->campus_id)
                                                    ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                    ->dehydrated(),
                                                Forms\Components\Select::make('reference')
                                                    ->label('How did you hear about us?')
                                                    ->options([
                                                        'Facebook' => 'Facebook',
                                                        'Friend / Family' => 'Friend / Family',
                                                        'Newspaper' => 'Newspaper',
                                                        'Other' => 'Other',
                                                    ])
                                                    ->default('Facebook'),
                                                Forms\Components\Select::make('academic_session_id')
                                                    ->relationship('academicSession', 'name')
                                                    ->label('Session'),
                                            ])->columns(4),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-student-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(2, 28),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 3: Parent or Guardian
                    Forms\Components\Wizard\Step::make('Parent or Guardian')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(3),
                                        Forms\Components\Section::make('Parent & Guardian Information')
                                            ->schema([
                                                Forms\Components\TextInput::make('father_name')
                                                    ->label("Father's or Guardian's Name")
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('father_cnic')
                                                    ->label('Father/Guardian CNIC #')
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('father_phone')
                                                    ->label("Father's Mobile Number")
                                                    ->tel(),
                                                Forms\Components\TextInput::make('mother_cnic')
                                                    ->label("Mother's CNIC #")
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('mother_phone')
                                                    ->label("Mother's Contact #")
                                                    ->tel(),
                                                Forms\Components\TextInput::make('father_occupation')
                                                    ->label("Father's/Guardian's Occupation")
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('emergency_contact')
                                                    ->label('Emergency Contact Number')
                                                    ->tel(),
                                                Forms\Components\TextInput::make('workflow_metadata.emergency_contact_relation')
                                                    ->label('Emergency Contact Relationship'),
                                                Forms\Components\Select::make('workflow_metadata.guardian_relation')
                                                    ->label('Relationship to Student')
                                                    ->options([
                                                        'Father' => 'Father',
                                                        'Mother' => 'Mother',
                                                        'Guardian' => 'Guardian / Other',
                                                    ])
                                                    ->default('Father'),
                                                Forms\Components\Toggle::make('same_as_student_address')
                                                    ->label("Guardian's address is same as student's address")
                                                    ->dehydrated(false)
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                                        if ($state) {
                                                            $set('father_address', $get('address'));
                                                        }
                                                    }),
                                                Forms\Components\Textarea::make('father_address')
                                                    ->label("Guardian's Address")
                                                    ->disabled(fn (Forms\Get $get) => (bool) $get('same_as_student_address'))
                                                    ->dehydrated()
                                                    ->columnSpanFull(),
                                            ])->columns(3),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-guardian-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(3, 42),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 4: Academic Details
                    Forms\Components\Wizard\Step::make('Academic Details')
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(4),
                                        Forms\Components\Section::make('Academic Qualifications')
                                            ->description('Add academic details of the applicant')
                                            ->schema([
                                                Forms\Components\Repeater::make('academic_details')
                                                    ->label('')
                                                    ->addActionLabel('Add Another Qualification')
                                                    ->collapsible()
                                                    ->cloneable()
                                                    ->reorderable()
                                                    ->schema([
                                                        Forms\Components\Select::make('level')
                                                            ->label('Academic Level')
                                                            ->options([
                                                                'matric' => 'Matriculation / SSC',
                                                                'intermediate' => 'Intermediate / HSSC',
                                                                'graduation' => 'Graduation',
                                                            ])
                                                            ->required()
                                                            ->live(),

                                                        Forms\Components\Grid::make(3)
                                                            ->schema([
                                                                 Forms\Components\Select::make('degree_title')
                                                                    ->label('Degree Title')
                                                                    ->options(fn (Forms\Get $get) => match ($get('level')) {
                                                                        'matric' => [
                                                                            'Matric with Biology' => 'Matric with Biology',
                                                                            'Matric with Computer Science' => 'Matric with Computer Science',
                                                                            'Matric with Arts' => 'Matric with Arts',
                                                                        ],
                                                                        'intermediate' => [
                                                                            'FSc Pre-Medical' => 'FSc Pre-Medical',
                                                                            'FSc Pre-Engineering' => 'FSc Pre-Engineering',
                                                                            'ICS' => 'ICS',
                                                                            'I.Com' => 'I.Com',
                                                                            'FA (Arts)' => 'FA (Arts)',
                                                                        ],
                                                                        default => [],
                                                                    })
                                                                    ->searchable()
                                                                    ->visible(fn (Forms\Get $get) => in_array($get('level'), ['matric', 'intermediate']))
                                                                    ->placeholder('Select degree option'),

                                                                Forms\Components\TextInput::make('degree_title_custom')
                                                                    ->label('Degree Title')
                                                                    ->visible(fn (Forms\Get $get) => $get('level') === 'graduation')
                                                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('degree_title', $state))
                                                                    ->placeholder('e.g. BS Computer Science'),

                                                                Forms\Components\TextInput::make('board_university')
                                                                    ->label('Board / University'),

                                                                Forms\Components\TextInput::make('passing_year')
                                                                    ->label('Passing Year')
                                                                    ->numeric()
                                                                    ->minValue(1950)
                                                                    ->maxValue((int) now()->year),

                                                                Forms\Components\TextInput::make('roll_no')
                                                                    ->label('Roll Number'),

                                                                Forms\Components\TextInput::make('obtained_marks')
                                                                    ->label('Obtained Marks')
                                                                    ->numeric()
                                                                    ->minValue(0)
                                                                    ->lte('total_marks')
                                                                    ->live(onBlur: true)
                                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                                        $total = (float) $get('total_marks');
                                                                        $set('percentage', $total > 0
                                                                            ? number_format(((float) $get('obtained_marks') / $total) * 100, 2, '.', '')
                                                                            : null);
                                                                    }),

                                                                Forms\Components\TextInput::make('total_marks')
                                                                    ->label('Total Marks')
                                                                    ->numeric()
                                                                    ->minValue(1)
                                                                    ->default(1100)
                                                                    ->live(onBlur: true)
                                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                                        $total = (float) $get('total_marks');
                                                                        $set('percentage', $total > 0
                                                                            ? number_format(((float) $get('obtained_marks') / $total) * 100, 2, '.', '')
                                                                            : null);
                                                                    }),

                                                                Forms\Components\TextInput::make('grade')
                                                                    ->label('Division / Grade')
                                                                    ->placeholder('e.g. A+ or First'),
                                                                Forms\Components\TextInput::make('percentage')
                                                                    ->label('Percentage')
                                                                    ->suffix('%')
                                                                    ->disabled()
                                                                    ->dehydrated(),

                                                                Forms\Components\TextInput::make('biology_marks')
                                                                    ->label('Biology Marks')
                                                                    ->numeric()
                                                                    ->visible(fn (Forms\Get $get) => in_array($get('level'), ['matric', 'intermediate']))
                                                                    ->placeholder('Enter biology marks'),
                                                            ])
                                                            ->visible(fn (Forms\Get $get) => $get('level') !== null),
                                                    ])
                                                    ->columnSpanFull()
                                                    ->grid(1)
                                                    ->default([]),
                                            ]),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-academic-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(4, 57),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 5: Documents Vault
                    Forms\Components\Wizard\Step::make('Documents Vault')
                        ->icon('heroicon-o-document-duplicate')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(5),
                                        Forms\Components\Section::make('Upload Credentials')
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->extraAttributes(['class' => 'admission-document-grid'])
                                                    ->schema([
                                                        self::getDocumentCard('cnic_copy', 'cnic_copy_status', '1. Student CNIC / B-Form', 'CNIC Status', 'Drop file here or click to upload'),
                                                        self::getDocumentCard('father_cnic_copy', 'father_cnic_copy_status', '2. Father / Guardian CNIC', 'Father CNIC Status', 'Drop file here or click to upload'),
                                                        self::getDocumentCard('matric_copy', 'matric_copy_status', '3. Matric Certificate', 'Matric Status', 'Drop file here or click to upload'),
                                                        self::getDocumentCard('inter_copy', 'inter_copy_status', '4. Intermediate Certificate', 'Intermediate Status', 'Drop file here or click to upload'),
                                                        self::getDocumentCard('domicile_copy', 'domicile_copy_status', '5. Domicile Certificate', 'Domicile Status', 'Drop file here or click to upload'),
                                                        self::getDocumentCard('character_certificate_copy', 'character_certificate_copy_status', '6. Character Certificate', 'Character Status', 'Drop file here or click to upload'),
                                                    ]),

                                                Forms\Components\Textarea::make('missing_documents')
                                                    ->label('Missing Documents Notes')
                                                    ->placeholder('Enter any missing documents notes here...')
                                                    ->rows(3)
                                                    ->columnSpanFull(),
                                            ]),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-documents-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(5, 71),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 6: Course and Fee Plan
                    Forms\Components\Wizard\Step::make('Course & Fee Plan')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(6),
                                        Forms\Components\Section::make('Course Assignment')
                                            ->schema([
                                                Forms\Components\Grid::make(4)
                                                    ->schema([
                                                        Forms\Components\Select::make('campus_id')
                                                            ->relationship('campus', 'name')
                                                            ->required()
                                                            ->default(fn () => filament()->auth()->user()->campus_id)
                                                            ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                            ->dehydrated(),
                                                        Forms\Components\Select::make('academic_session_id')
                                                            ->relationship('academicSession', 'name')
                                                            ->label('Academic Session')
                                                            ->required(),
                                                        Forms\Components\Select::make('course_id')
                                                            ->relationship('course', 'name')
                                                            ->label('Assigned Course / Program')
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                                if (! $state) {
                                                                    return;
                                                                }
                                                                $course = Course::find($state);
                                                                if (! $course) {
                                                                    return;
                                                                }

                                                                $structure = app(OfficialFeeStructureResolver::class)->resolve(
                                                                    (int) $state,
                                                                    $get('campus_id'),
                                                                    $get('academic_session_id'),
                                                                    $get('admission_date'),
                                                                );
                                                                if ($structure) {
                                                                    $set('custom_tuition_fee', $structure->total_fee);
                                                                    $set('custom_installment_count', $structure->installment_count ?: 12);
                                                                } else {
                                                                    $set('custom_tuition_fee', 0.00);
                                                                    $set('custom_installment_count', 12);
                                                                }

                                                                // Fetch course-specific fee heads
                                                                $admissionHead = FeeHead::where('course_id', $state)->where('category', 'admission')->first();
                                                                $set('custom_admission_fee', $admissionHead?->default_amount ?: 0.00);

                                                                $verificationHead = FeeHead::where('course_id', $state)->where('code', 'like', 'VERIFICATION_%')->first();
                                                                $set('custom_verification_fee', $verificationHead?->default_amount ?: 0.00);

                                                                $endowmentHead = FeeHead::where('course_id', $state)->where('category', 'affiliation')->first();
                                                                $set('custom_enrollment_fee', $endowmentHead?->default_amount ?: 0.00);

                                                                $examHead = FeeHead::where('course_id', $state)->where('code', 'like', 'EXAM_%')->first();
                                                                $set('custom_examination_fee', $examHead?->default_amount ?: 0.00);

                                                                $miscHead = FeeHead::where('course_id', $state)->where('category', 'miscellaneous')->first();
                                                                $hostelHead = FeeHead::where('course_id', $state)->where('category', 'hostel')->first();
                                                                $totalMisc = ($miscHead?->default_amount ?: 0.00) + ($hostelHead?->default_amount ?: 0.00);
                                                                $set('custom_other_misc', $totalMisc);
                                                            }),
                                                        Forms\Components\Placeholder::make('official_fee_plan_notice')
                                                            ->label('')
                                                            ->content(function (Forms\Get $get) {
                                                                $course = Course::find($get('course_id'));
                                                                $session = AcademicSession::find($get('academic_session_id'));
                                                                $structure = $course
                                                                    ? app(OfficialFeeStructureResolver::class)->resolve(
                                                                        (int) $course->id,
                                                                        $get('campus_id'),
                                                                        $get('academic_session_id'),
                                                                        $get('admission_date'),
                                                                    )
                                                                    : null;

                                                                return $course
                                                                    ? "Official fee plan v{$structure?->version} loaded for {$course->name} — ".($session?->name ?: 'session pending')
                                                                    : 'Select a course to load its approved official fee plan.';
                                                            })
                                                            ->columnSpanFull(),
                                                        Forms\Components\DatePicker::make('admission_date')
                                                            ->label('Admission Date')
                                                            ->default(now())
                                                            ->required(),
                                                    ]),
                                            ]),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Section::make('One-time Charges')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('custom_admission_fee')
                                                            ->label('Admission Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                            ->dehydrated()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('custom_verification_fee')
                                                            ->label('Verification Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                            ->dehydrated()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('custom_enrollment_fee')
                                                            ->label('Enrollment Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                            ->dehydrated()
                                                            ->required(),
                                                    ])
                                                    ->columnSpan(1),

                                                Forms\Components\Section::make('Recurring Tuition')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('custom_tuition_fee')
                                                            ->label('Tuition Fee Total')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                            ->dehydrated()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('custom_installment_count')
                                                            ->label('Number of Installments')
                                                            ->numeric()
                                                            ->live()
                                                            ->required(),
                                                        Forms\Components\Placeholder::make('monthly_installment_preview')
                                                            ->label('Monthly Installment Amount')
                                                            ->content(function (Forms\Get $get) {
                                                                $tuition = (float) $get('custom_tuition_fee');
                                                                $installments = (int) $get('custom_installment_count') ?: 12;
                                                                $concession = (float) $get('concession_amount');
                                                                $perInstallment = $installments > 0 ? round(($tuition - $concession) / $installments, 2) : 0;

                                                                return new HtmlString("<div class='p-3 bg-amber-50 rounded-lg text-center font-bold text-amber-700'>PKR ".number_format($perInstallment, 2).'</div>');
                                                            }),
                                                    ])
                                                    ->columnSpan(1),

                                                Forms\Components\Section::make('Additional Charges')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('custom_examination_fee')
                                                            ->label('Examination Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                            ->dehydrated()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('custom_other_misc')
                                                            ->label('Other / Miscellaneous Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                            ->dehydrated()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('reference')
                                                            ->label('Other Charge Description')
                                                            ->placeholder('e.g. ID Card, Library Fee'),
                                                    ])
                                                    ->columnSpan(1),
                                            ]),

                                        Forms\Components\Section::make('Scholarship / Fee Concession')
                                            ->schema([
                                                Forms\Components\Select::make('concession_type')
                                                    ->label('Concession Type')
                                                    ->options([
                                                        'none' => 'None',
                                                        'merit' => 'Merit Scholarship',
                                                        'need' => 'Need-based concession',
                                                        'sibling' => 'Kinship / Sibling concession',
                                                        'special' => 'Special discount / Orphan scholarship',
                                                    ])
                                                    ->default('none')
                                                    ->live(),
                                                Forms\Components\TextInput::make('concession_amount')
                                                    ->label('Approved Concession Amount')
                                                    ->numeric()
                                                    ->prefix('PKR')
                                                    ->default(0.00)
                                                    ->live()
                                                    ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none'),
                                                Forms\Components\Select::make('concession_value_type')
                                                    ->label('Calculation Method')
                                                    ->options([
                                                        'fixed' => 'Fixed Amount',
                                                        'percentage' => 'Percentage',
                                                    ])
                                                    ->default('fixed')
                                                    ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none'),
                                                Forms\Components\TextInput::make('concession_value')
                                                    ->label('Requested Value')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none'),
                                                Forms\Components\TextInput::make('concession_approver')
                                                    ->label('Approving Authority / Officer')
                                                    ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none'),
                                                Forms\Components\TextInput::make('workflow_metadata.concession_approval_reference')
                                                    ->label('Approval Reference')
                                                    ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none'),
                                                Forms\Components\Select::make('concession_status')
                                                    ->label('Approval Status')
                                                    ->options([
                                                        'draft' => 'Draft',
                                                        'pending' => 'Pending Approval',
                                                        'approved' => 'Approved',
                                                        'rejected' => 'Rejected',
                                                        'cancelled' => 'Cancelled',
                                                    ])
                                                    ->default('pending')
                                                    ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                    ->dehydrated(),
                                                Forms\Components\Textarea::make('concession_reason')
                                                    ->label('Reason / Supporting Notes')
                                                    ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none')
                                                    ->columnSpanFull(),
                                            ])->columns(3),

                                        Forms\Components\Placeholder::make('live_total_preview')
                                            ->label('Live Customized Fee Summary')
                                            ->content(function (Forms\Get $get) {
                                                $tuition = (float) $get('custom_tuition_fee');
                                                $admission = (float) $get('custom_admission_fee');
                                                $enrollment = (float) $get('custom_enrollment_fee');
                                                $verification = (float) $get('custom_verification_fee');
                                                $exam = (float) $get('custom_examination_fee');
                                                $misc = (float) $get('custom_other_misc');
                                                $installments = (int) $get('custom_installment_count') ?: 12;
                                                $concession = (float) $get('concession_amount');

                                                $totalPackage = $tuition + $admission + $enrollment + $verification + $exam + $misc;
                                                $netPayable = max(0, $totalPackage - $concession);
                                                $perInstallment = $installments > 0 ? round(($tuition - $concession) / $installments, 2) : 0;
                                                if ($perInstallment < 0) {
                                                    $perInstallment = 0;
                                                }

                                                return new HtmlString(sprintf(
                                                    '<div class="p-5 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                                                        <div class="text-sm font-bold text-slate-800 border-b pb-2">Live Fee Summary (Real-time calculation)</div>
                                                        <div class="grid grid-cols-2 gap-4 text-xs">
                                                            <div class="space-y-1">
                                                                <div><strong>Official Package Total:</strong> PKR %s</div>
                                                                <div><strong>Concession Amount:</strong> -PKR %s</div>
                                                                <div class="text-emerald-700 font-bold text-sm">Net Payable Amount: PKR %s</div>
                                                            </div>
                                                            <div class="space-y-1">
                                                                <div><strong>Payable at Admission:</strong> PKR %s</div>
                                                                <div><strong>Remaining Balance:</strong> PKR %s</div>
                                                                <div class="text-xs text-slate-500 font-bold">%d Installments x PKR %s</div>
                                                            </div>
                                                        </div>
                                                        <div class="border-t pt-3 mt-3 flex items-center justify-between text-xs text-slate-600">
                                                            <span>Installment Timeline: Admission → tuition installments → examination dues</span>
                                                        </div>
                                                    </div>',
                                                    number_format($totalPackage, 2),
                                                    number_format($concession, 2),
                                                    number_format($netPayable, 2),
                                                    number_format($admission + ($tuition / $installments), 2),
                                                    number_format($netPayable - ($admission + ($tuition / $installments)), 2),
                                                    $installments,
                                                    number_format($perInstallment, 2)
                                                ));
                                            })
                                            ->columnSpanFull(),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('previewAllVouchers')
                                                ->label('Preview All Vouchers')
                                                ->icon('heroicon-o-eye')
                                                ->color('gray')
                                                ->modalHeading('Read-only Voucher Preview')
                                                ->modalWidth('6xl')
                                                ->modalSubmitAction(false)
                                                ->modalCancelActionLabel('Close Preview')
                                                ->modalContent(function (Forms\Get $get) {
                                                    $money = app(InstallmentPlanGenerator::class);
                                                    $oneTimePaisa = collect([
                                                        $get('custom_admission_fee'),
                                                        $get('custom_enrollment_fee'),
                                                        $get('custom_verification_fee'),
                                                        $get('custom_other_misc'),
                                                    ])->sum(fn ($amount) => $money->toPaisa($amount ?: 0));
                                                    $schedule = app(VoucherGenerationService::class)->previewPlan([
                                                        'tuition' => $get('custom_tuition_fee') ?: 0,
                                                        'one_time' => number_format($oneTimePaisa / 100, 2, '.', ''),
                                                        'examination' => $get('custom_examination_fee') ?: 0,
                                                        'concession' => $get('concession_amount') ?: 0,
                                                        'installment_count' => $get('custom_installment_count') ?: 1,
                                                        'admission_date' => $get('admission_date') ?: now(),
                                                    ]);

                                                    return view('admissions.voucher-preview', compact('schedule'));
                                                }),
                                        ])->columnSpanFull(),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-fee-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(6, 85),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 7: Review and Confirm
                    Forms\Components\Wizard\Step::make('Review & Confirm')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(7),
                                        Forms\Components\Placeholder::make('review_summary')
                                            ->label('')
                                            ->content(function (Forms\Get $get) {
                                                $courseId = $get('course_id');
                                                $campusId = $get('campus_id');
                                                $sessionId = $get('academic_session_id');
                                                $documents = collect([
                                                    $get('cnic_copy'),
                                                    $get('father_cnic_copy'),
                                                    $get('matric_copy'),
                                                    $get('inter_copy'),
                                                    $get('domicile_copy'),
                                                    $get('character_certificate_copy'),
                                                ])->filter()->count();
                                                $totalFee = collect([
                                                    $get('custom_tuition_fee'),
                                                    $get('custom_admission_fee'),
                                                    $get('custom_enrollment_fee'),
                                                    $get('custom_verification_fee'),
                                                    $get('custom_examination_fee'),
                                                    $get('custom_other_misc'),
                                                ])->sum(fn ($amount) => (float) $amount);

                                                return view('filament.admissions.components.review-summary', [
                                                    'studentName' => $get('applicant_name') ?: 'Not entered yet',
                                                    'cnic' => $get('cnic') ?: 'Not entered yet',
                                                    'dob' => $get('dob') ?: 'Not entered yet',
                                                    'gender' => filled($get('gender')) ? ucfirst((string) $get('gender')) : 'Not entered yet',
                                                    'guardian' => $get('father_name') ?: 'Not entered yet',
                                                    'guardianPhone' => $get('father_phone') ?: 'Not entered yet',
                                                    'qualificationCount' => count($get('academic_details') ?: []),
                                                    'documentCount' => $documents,
                                                    'course' => $courseId ? (Course::find($courseId)?->name ?: 'Not selected yet') : 'Not selected yet',
                                                    'campus' => $campusId ? (Campus::find($campusId)?->name ?: 'Not selected yet') : 'Not selected yet',
                                                    'session' => $sessionId ? (AcademicSession::find($sessionId)?->name ?: 'Not selected yet') : 'Not selected yet',
                                                    'shift' => filled($get('shift')) ? ucfirst((string) $get('shift')) : 'Not selected yet',
                                                    'totalFee' => $totalFee,
                                                    'installments' => (int) ($get('custom_installment_count') ?: 0),
                                                ]);
                                            })
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('status')
                                            ->label('Initial Operational Status')
                                            ->options([
                                                'draft' => 'Draft / Incomplete',
                                                'submitted' => 'Submitted / Complete',
                                                'under_review' => 'Under Review',
                                                'documents_pending' => 'Documents Pending',
                                                'fee_pending' => 'Fee Pending',
                                                'approved' => 'Approved / Awaiting Enrollment',
                                                'rejected' => 'Rejected',
                                                'cancelled' => 'Cancelled',
                                            ])
                                            ->default('submitted')
                                            ->required(),
                                        Forms\Components\Checkbox::make('declaration_accepted')
                                            ->label('I confirm that I have reviewed the student information, guardian details, academic qualifications, documents, course assignment, fee plan, concession details, and installment schedule.')
                                            ->accepted()
                                            ->dehydrated(false)
                                            ->columnSpanFull(),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-review-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(7, 100),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),
                ])
                    ->nextAction(fn (Forms\Components\Actions\Action $action) => $action
                        ->label('Save & Continue')
                        ->icon('heroicon-o-arrow-right')
                        ->extraAttributes(['class' => 'admission-next-action']))
                    ->previousAction(fn (Forms\Components\Actions\Action $action) => $action
                        ->label('Back')
                        ->icon('heroicon-o-arrow-left')
                        ->extraAttributes(['class' => 'admission-back-action']))
                    ->persistStepInQueryString()
                    ->extraAttributes(['class' => 'admission-wizard'])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\TextColumn::make('applicant_name')
                    ->label('Applicant Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('missing_docs')
                    ->label('Docs Missing')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('warning')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success')
                    ->state(fn ($record) => empty($record->cnic_copy) || empty($record->matric_copy) || empty($record->domicile_copy)),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicSession.name')
                    ->label('Session')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Contact')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => fn ($state) => in_array($state, ['draft', 'cancelled', 'waitlisted']),
                        'info' => 'submitted',
                        'warning' => 'under_review',
                        'danger' => fn ($state) => in_array($state, ['documents_pending', 'fee_pending', 'rejected']),
                        'success' => fn ($state) => in_array($state, ['approved', 'enrolled']),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('campus', 'name')
                    ->hidden(fn () => filament()->getCurrentPanel()?->getId() === 'campus'),
                Tables\Filters\SelectFilter::make('academicSession')
                    ->relationship('academicSession', 'name'),
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\Action::make('approveAndEnroll')
                    ->label('Approve & Enroll')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'enrolled')
                    ->action(function ($record) {
                        try {
                            EnrollmentService::enroll($record, filament()->auth()->id());
                            Notification::make()
                                ->title('Enrolled Successfully')
                                ->body('Student has been registered and fee ledger vouchers generated.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Enrollment Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('printAgreement')
                    ->label('Print Agreement')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn ($record) => route('pdf.admission-agreement', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('downloadForm')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => route('pdf.admission-letter', $record))
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

        if (! filament()->auth()->user()->hasRole('Super Admin')) {
            $query->where('campus_id', filament()->auth()->user()->campus_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmissions::route('/'),
            'create' => Pages\CreateAdmission::route('/create'),
            'edit' => Pages\EditAdmission::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdmissionResource\Pages;
use App\Models\AcademicSession;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Support\DashboardImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;

class AdmissionResource extends Resource
{
    protected static ?string $model = Admission::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Student Relations';

    protected static ?int $navigationSort = 1;

    public static array $pakistanCities = [
        'Okara' => 'Okara',
        'Okara Cantonment' => 'Okara Cantonment',
        'Sahiwal' => 'Sahiwal',
        'Pakpattan' => 'Pakpattan',
        'Lahore' => 'Lahore',
        'Multan' => 'Multan',
        'Faisalabad' => 'Faisalabad',
        'Islamabad' => 'Islamabad',
        'Rawalpindi' => 'Rawalpindi',
        'Karachi' => 'Karachi',
        'Peshawar' => 'Peshawar',
        'Quetta' => 'Quetta',
        'Gujranwala' => 'Gujranwala',
        'Sargodha' => 'Sargodha',
        'Bahawalpur' => 'Bahawalpur',
        'DG Khan' => 'DG Khan',
        'Sheikhupura' => 'Sheikhupura',
        'Kasur' => 'Kasur',
        'Jhang' => 'Jhang',
        'Vehari' => 'Vehari',
        'Rahim Yar Khan' => 'Rahim Yar Khan',
        'Attock' => 'Attock',
        'Chiniot' => 'Chiniot',
        'Gujrat' => 'Gujrat',
        'Hafizabad' => 'Hafizabad',
        'Khanewal' => 'Khanewal',
        'Layyah' => 'Layyah',
        'Lodhran' => 'Lodhran',
        'Mianwali' => 'Mianwali',
        'Muzaffargarh' => 'Muzaffargarh',
        'Nankana Sahib' => 'Nankana Sahib',
        'Narowal' => 'Narowal',
        'Rajanpur' => 'Rajanpur',
        'Sialkot' => 'Sialkot',
        'Toba Tek Singh' => 'Toba Tek Singh',
        'Other' => 'Other City',
    ];

    public static array $pakistanDistricts = [
        'Okara' => 'Okara',
        'Sahiwal' => 'Sahiwal',
        'Pakpattan' => 'Pakpattan',
        'Lahore' => 'Lahore',
        'Multan' => 'Multan',
        'Faisalabad' => 'Faisalabad',
        'Rawalpindi' => 'Rawalpindi',
        'Islamabad' => 'Islamabad',
        'Gujranwala' => 'Gujranwala',
        'Sargodha' => 'Sargodha',
        'Bahawalpur' => 'Bahawalpur',
        'DG Khan' => 'Dera Ghazi Khan',
        'Sheikhupura' => 'Sheikhupura',
        'Kasur' => 'Kasur',
        'Jhang' => 'Jhang',
        'Vehari' => 'Vehari',
        'Rahim Yar Khan' => 'Rahim Yar Khan',
        'Attock' => 'Attock',
        'Chiniot' => 'Chiniot',
        'Gujrat' => 'Gujrat',
        'Hafizabad' => 'Hafizabad',
        'Khanewal' => 'Khanewal',
        'Layyah' => 'Layyah',
        'Lodhran' => 'Lodhran',
        'Mianwali' => 'Mianwali',
        'Muzaffargarh' => 'Muzaffargarh',
        'Nankana Sahib' => 'Nankana Sahib',
        'Narowal' => 'Narowal',
        'Rajanpur' => 'Rajanpur',
        'Sialkot' => 'Sialkot',
        'Toba Tek Singh' => 'Toba Tek Singh',
        'Other' => 'Other District',
    ];

    public static array $biseBoards = [
        'BISE Sahiwal' => 'BISE Sahiwal',
        'BISE Lahore' => 'BISE Lahore',
        'BISE Multan' => 'BISE Multan',
        'BISE Faisalabad' => 'BISE Faisalabad',
        'BISE Gujranwala' => 'BISE Gujranwala',
        'BISE Rawalpindi' => 'BISE Rawalpindi',
        'BISE Sargodha' => 'BISE Sargodha',
        'BISE Bahawalpur' => 'BISE Bahawalpur',
        'BISE DG Khan' => 'BISE DG Khan',
        'Federal Board (FBISE) Islamabad' => 'Federal Board (FBISE) Islamabad',
        'Aga Khan Examination Board' => 'Aga Khan Examination Board',
        'PBTE (Technical Board) Lahore' => 'PBTE (Technical Board) Lahore',
        'Other Board' => 'Other Board',
    ];

    public static array $pakistanUniversities = [
        'University of Okara' => 'University of Okara',
        'Bahauddin Zakariya University (BZU) Multan' => 'Bahauddin Zakariya University (BZU) Multan',
        'University of the Punjab (PU) Lahore' => 'University of the Punjab (PU) Lahore',
        'GC University (GCU) Lahore' => 'GC University (GCU) Lahore',
        'UET Lahore' => 'UET Lahore',
        'Islamia University Bahawalpur (IUB)' => 'Islamia University Bahawalpur (IUB)',
        'University of Agriculture Faisalabad (UAF)' => 'University of Agriculture Faisalabad (UAF)',
        'University of Health Sciences (UHS) Lahore' => 'University of Health Sciences (UHS) Lahore',
        'Virtual University of Pakistan' => 'Virtual University of Pakistan',
        'Allama Iqbal Open University (AIOU)' => 'Allama Iqbal Open University (AIOU)',
        'Other University' => 'Other University',
    ];

    /**
     * Load shared wizard lookup lists once per request.
     */
    protected static function getAdmissionLookups(): array
    {
        return once(fn (): array => [
            'courses' => Course::query()->orderBy('name')->pluck('name', 'id')->all(),
            'campuses' => Campus::query()->orderBy('name')->pluck('name', 'id')->all(),
            'sessions' => AcademicSession::query()->orderByDesc('start_date')->pluck('name', 'id')->all(),
        ]);
    }

    protected static function getSidebarPlaceholder(int $stepIndex, int $percentage): Forms\Components\Placeholder
    {
        return Forms\Components\Placeholder::make("admission_sidebar_step_{$stepIndex}")
            ->label('')
            ->content(function (Forms\Get $get) use ($stepIndex, $percentage) {
                $lookups = self::getAdmissionLookups();

                $courseId = $get('course_id');
                $campusId = $get('campus_id');
                $sessionId = $get('academic_session_id');

                return view('filament.admissions.components.context-panel', [
                    'stepIndex' => $stepIndex,
                    'percentage' => $percentage,
                    'studentName' => $get('applicant_name') ?: 'Not entered yet',
                    'course' => $courseId ? ($lookups['courses'][$courseId] ?? 'Not selected yet') : 'Not selected yet',
                    'campus' => $campusId ? ($lookups['campuses'][$campusId] ?? 'Not selected yet') : 'Not selected yet',
                    'session' => $sessionId ? ($lookups['sessions'][$sessionId] ?? 'Not selected yet') : 'Not selected yet',
                    'shift' => filled($get('shift')) ? ucfirst((string) $get('shift')) : 'Not selected yet',
                ]);
            })
            ->extraAttributes(['class' => 'admission-context-placeholder']);
    }

    protected static function getStepIntroPlaceholder(int $stepIndex): Forms\Components\Placeholder
    {
        $steps = [
            1 => ['Student & Guardian Profile', 'Upload photo, personal details, contact information, and parent/guardian details.', 'heroicon-o-user'],
            2 => ['Academic Qualifications', "Record the applicant's previous qualifications, BISE boards/universities, and marks.", 'heroicon-o-academic-cap'],
            3 => ['Documents Vault', 'Upload student and guardian CNIC copies (front/back) and certificates.', 'heroicon-o-folder-open'],
            4 => ['Course & Fee Plan', 'Assign course, configure tuition/admission fees, and customize installment schedule.', 'heroicon-o-banknotes'],
            5 => ['Review & Confirm', 'Review all details before submitting the admission.', 'heroicon-o-shield-check'],
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
            ->columns(1)
            ->schema([
                Forms\Components\Placeholder::make('missing_docs_notice')
                    ->label('⚠️ Document Status')
                    ->content('Notice: Some required documents (CNIC copy, Matric certificate copy, or Domicile copy) are missing for this applicant. Please upload them to complete the record.')
                    ->visible(fn ($record) => $record && ($record->status === 'documents_pending' || empty($record->cnic_copy) || empty($record->matric_copy) || empty($record->domicile_copy)))
                    ->columnSpanFull(),

                Forms\Components\Wizard::make([
                    // Step 1: Student & Guardian Profile
                    Forms\Components\Wizard\Step::make('Student & Guardian Profile')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(1),
                                        Forms\Components\Section::make('Personal Identity & Student Photo')
                                             ->schema([
                                                 Forms\Components\FileUpload::make('student_photo')
                                                     ->label('Student Photo')
                                                     ->disk('public')
                                                     ->directory('student-photos')
                                                     ->image()
                                                     ->imagePreviewHeight('100')
                                                     ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                                     ->maxSize(4096)
                                                     ->helperText('Passport-size photo (Max 4MB)')
                                                     ->columnSpan(1),

                                                 Forms\Components\Grid::make(3)
                                                     ->columnSpan(3)
                                                     ->schema([
                                                         Forms\Components\TextInput::make('applicant_name')
                                                             ->label('Student Full Name')
                                                             ->default('Draft Student')
                                                             ->required()
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
                                                             ])
                                                             ->default('male'),
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
                                                     ]),
                                             ])->columns(4),

                                        Forms\Components\Section::make('Contact Details')
                                            ->schema([
                                                Forms\Components\TextInput::make('phone')
                                                    ->label('Mobile Number')
                                                    ->tel()
                                                    ->helperText('Format: 03001234567'),
                                                Forms\Components\TextInput::make('email')
                                                    ->label('Email Address (Optional)')
                                                    ->email()
                                                    ->maxLength(255),
                                                Forms\Components\Select::make('city')
                                                    ->label('City')
                                                    ->searchable()
                                                    ->options(self::$pakistanCities)
                                                    ->default('Okara'),
                                                Forms\Components\Select::make('domicile_district')
                                                    ->label('Domicile (District)')
                                                    ->searchable()
                                                    ->options(self::$pakistanDistricts)
                                                    ->default('Okara'),
                                                Forms\Components\Textarea::make('address')
                                                    ->label('Current Residential Address')
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ])->columns(4),

                                        Forms\Components\Section::make('Admission Preferences')
                                            ->schema([
                                                Forms\Components\Select::make('shift')
                                                    ->label('Shift / Category Preference')
                                                    ->options([
                                                        'morning' => 'Morning Shift',
                                                        'evening' => 'Evening Shift',
                                                        'weekend' => 'Weekend Shift',
                                                        'offline' => 'Private / Offline (Exams Only)',
                                                    ])
                                                    ->default('morning'),
                                                Forms\Components\Select::make('campus_id')
                                                    ->options(fn () => self::getAdmissionLookups()['campuses'])
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
                                                    ->options(fn () => self::getAdmissionLookups()['sessions'])
                                                    ->label('Academic Session'),
                                            ])->columns(4),

                                        Forms\Components\Section::make('Parent & Guardian Details')
                                            ->schema([
                                                Forms\Components\TextInput::make('father_name')
                                                    ->label("Father's / Guardian's Full Name")
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('father_cnic')
                                                    ->label('Father / Guardian CNIC #')
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('father_phone')
                                                    ->label("Father's / Guardian's Mobile Number")
                                                    ->tel(),
                                                Forms\Components\TextInput::make('emergency_contact')
                                                    ->label('Emergency Contact Number')
                                                    ->tel(),
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
                                    ])->extraAttributes(['class' => 'admission-main-column admission-student-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(1, 20),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 2: Academic Details
                    Forms\Components\Wizard\Step::make('Academic Qualifications')
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(2),
                                        Forms\Components\Section::make('Previous Academic Qualifications')
                                            ->description('Add academic details of the applicant (Matric, Intermediate, Graduation)')
                                            ->schema([
                                                Forms\Components\Repeater::make('academic_details')
                                                    ->label('')
                                                    ->addActionLabel('+ Add Qualification')
                                                    ->collapsible()
                                                    ->cloneable()
                                                    ->reorderable()
                                                    ->schema([
                                                        Forms\Components\Select::make('level')
                                                            ->label('Academic Level')
                                                            ->options([
                                                                'matric' => 'Matriculation / SSC',
                                                                'intermediate' => 'Intermediate / HSSC',
                                                                'graduation' => 'Graduation / Degree',
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
                                                                    ->visible(fn (Forms\Get $get) => in_array($get('level'), ['matric', 'intermediate'])),

                                                                Forms\Components\TextInput::make('degree_title_custom')
                                                                    ->label('Degree Title')
                                                                    ->visible(fn (Forms\Get $get) => $get('level') === 'graduation')
                                                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('degree_title', $state))
                                                                    ->placeholder('e.g. BS Computer Science'),

                                                                Forms\Components\Select::make('board_university')
                                                                    ->label('Board / University')
                                                                    ->searchable()
                                                                    ->options(fn (Forms\Get $get) => match ($get('level')) {
                                                                        'graduation' => self::$pakistanUniversities,
                                                                        default => self::$biseBoards,
                                                                    }),

                                                                Forms\Components\Select::make('passing_year')
                                                                    ->label('Passing Year')
                                                                    ->searchable()
                                                                    ->options(array_combine(range((int) now()->year, 1990), range((int) now()->year, 1990))),

                                                                Forms\Components\TextInput::make('roll_no')
                                                                    ->label('Roll Number'),

                                                                Forms\Components\TextInput::make('obtained_marks')
                                                                    ->label('Obtained Marks')
                                                                    ->numeric()
                                                                    ->minValue(0)
                                                                    ->live(onBlur: true)
                                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                                        $obtained = (float) $get('obtained_marks');
                                                                        $total = (float) $get('total_marks');
                                                                        if ($total > 0 && $obtained >= 0) {
                                                                            $pct = ($obtained / $total) * 100;
                                                                            $set('percentage', number_format($pct, 2, '.', ''));
                                                                            if ($pct >= 80) {
                                                                                $set('grade', 'A+ / 1st Div');
                                                                            } elseif ($pct >= 70) {
                                                                                $set('grade', 'A / 1st Div');
                                                                            } elseif ($pct >= 60) {
                                                                                $set('grade', 'B / 1st Div');
                                                                            } elseif ($pct >= 50) {
                                                                                $set('grade', 'C / 2nd Div');
                                                                            } elseif ($pct >= 33) {
                                                                                $set('grade', 'D / 3rd Div');
                                                                            } else {
                                                                                $set('grade', 'F / Fail');
                                                                            }
                                                                        }
                                                                    }),

                                                                Forms\Components\TextInput::make('total_marks')
                                                                    ->label('Total Marks')
                                                                    ->numeric()
                                                                    ->minValue(1)
                                                                    ->default(1100)
                                                                    ->live(onBlur: true)
                                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                                        $obtained = (float) $get('obtained_marks');
                                                                        $total = (float) $get('total_marks');
                                                                        if ($total > 0 && $obtained >= 0) {
                                                                            $pct = ($obtained / $total) * 100;
                                                                            $set('percentage', number_format($pct, 2, '.', ''));
                                                                            if ($pct >= 80) {
                                                                                $set('grade', 'A+ / 1st Div');
                                                                            } elseif ($pct >= 70) {
                                                                                $set('grade', 'A / 1st Div');
                                                                            } elseif ($pct >= 60) {
                                                                                $set('grade', 'B / 1st Div');
                                                                            } elseif ($pct >= 50) {
                                                                                $set('grade', 'C / 2nd Div');
                                                                            } elseif ($pct >= 33) {
                                                                                $set('grade', 'D / 3rd Div');
                                                                            } else {
                                                                                $set('grade', 'F / Fail');
                                                                            }
                                                                        }
                                                                    }),

                                                                Forms\Components\TextInput::make('grade')
                                                                    ->label('Division & Grade (Auto)')
                                                                    ->placeholder('Auto calculated')
                                                                    ->dehydrated(),

                                                                Forms\Components\TextInput::make('percentage')
                                                                    ->label('Percentage')
                                                                    ->suffix('%')
                                                                    ->placeholder('Auto calculated')
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
                                        self::getSidebarPlaceholder(2, 40),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 3: Documents Vault
                    Forms\Components\Wizard\Step::make('Documents Vault')
                        ->icon('heroicon-o-folder-open')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(3),
                                        Forms\Components\Section::make('Upload Student & Guardian Documents')
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->extraAttributes(['class' => 'admission-document-grid'])
                                                    ->schema([
                                                        self::getDocumentCard('student_cnic_front', 'student_cnic_front_status', '1. Student CNIC / B-Form (Front)', 'CNIC Front Status', 'Drop front image here'),
                                                        self::getDocumentCard('student_cnic_back', 'student_cnic_back_status', '2. Student CNIC / B-Form (Back)', 'CNIC Back Status', 'Drop back image here'),
                                                        self::getDocumentCard('father_cnic_front', 'father_cnic_front_status', '3. Father / Guardian CNIC (Front)', 'Father Front Status', 'Drop front image here'),
                                                        self::getDocumentCard('father_cnic_back', 'father_cnic_back_status', '4. Father / Guardian CNIC (Back)', 'Father Back Status', 'Drop back image here'),
                                                        self::getDocumentCard('matric_copy', 'matric_copy_status', '5. Matric Certificate Copy', 'Matric Status', 'Drop file here'),
                                                        self::getDocumentCard('inter_copy', 'inter_copy_status', '6. Intermediate Certificate Copy', 'Inter Status', 'Drop file here'),
                                                        self::getDocumentCard('domicile_copy', 'domicile_copy_status', '7. Domicile Certificate', 'Domicile Status', 'Drop file here'),
                                                        self::getDocumentCard('character_certificate_copy', 'character_certificate_copy_status', '8. Character Certificate', 'Character Status', 'Drop file here'),
                                                    ]),

                                                Forms\Components\Textarea::make('missing_documents')
                                                    ->label('Missing Documents Notes (Optional)')
                                                    ->placeholder('Enter any missing document notes here...')
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ]),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-documents-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(3, 60),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 4: Course and Fee Plan
                    Forms\Components\Wizard\Step::make('Course & Fee Plan')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(4),
                                        Forms\Components\Section::make('Course Assignment')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\Select::make('campus_id')
                                                            ->options(fn () => self::getAdmissionLookups()['campuses'])
                                                            ->required()
                                                            ->default(fn () => filament()->auth()->user()->campus_id)
                                                            ->disabled(fn () => ! filament()->auth()->user()->hasRole('Super Admin'))
                                                            ->dehydrated(),
                                                        Forms\Components\Select::make('academic_session_id')
                                                            ->options(fn () => self::getAdmissionLookups()['sessions'])
                                                            ->label('Academic Session')
                                                            ->required(),
                                                        Forms\Components\Select::make('course_id')
                                                            ->options(fn () => self::getAdmissionLookups()['courses'])
                                                            ->label('Assigned Course / Program')
                                                            ->required()
                                                            ->extraInputAttributes(function (): array {
                                                                $previewUrl = Js::from(route('admin.admissions.fee-plan-preview'));

                                                                return [
                                                                    'x-on:change' => str_replace('__PREVIEW_URL__', (string) $previewUrl, <<<'JS'
                                                                    const courseId = $event.target.value

                                                                    if (! courseId) {
                                                                        return
                                                                    }

                                                                    const params = new URLSearchParams({
                                                                        course_id: courseId,
                                                                        campus_id: $wire.data.campus_id ?? '',
                                                                        academic_session_id: $wire.data.academic_session_id ?? '',
                                                                        admission_date: $wire.data.admission_date ?? '',
                                                                    })

                                                                    fetch(__PREVIEW_URL__ + '?' + params.toString(), {
                                                                        headers: { Accept: 'application/json' },
                                                                    })
                                                                        .then((response) => {
                                                                            if (! response.ok) {
                                                                                throw new Error('Fee plan preview failed')
                                                                            }

                                                                            return response.json()
                                                                        })
                                                                        .then((plan) => {
                                                                            Object.entries(plan).forEach(([field, value]) => {
                                                                                $wire.$set(`data.${field}`, value, false)
                                                                            })
                                                                        })
                                                                        .catch(() => {})
                                                                JS),
                                                                ];
                                                            }),
                                                        Forms\Components\DatePicker::make('admission_date')
                                                            ->label('Admission Date')
                                                            ->default(now())
                                                            ->required(),
                                                    ]),
                                            ]),

                                        Forms\Components\Section::make('Fee Structure Breakdown')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('custom_tuition_fee')
                                                            ->label('Total Course Tuition Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->required(),

                                                        Forms\Components\Select::make('workflow_metadata.applicable_charge_type')
                                                            ->label('Applicable Extra Charges')
                                                            ->options([
                                                                'admission' => 'Admission Fee Only',
                                                                'examination' => 'Examination Fee Only',
                                                                'both' => 'Admission + Examination Fee',
                                                                'none' => 'Tuition Fee Only',
                                                            ])
                                                            ->default('admission'),

                                                        Forms\Components\TextInput::make('custom_admission_fee')
                                                            ->label('Admission Fee Amount')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->default(0.00),

                                                        Forms\Components\TextInput::make('custom_examination_fee')
                                                            ->label('Examination Fee Amount')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->default(0.00),

                                                        Forms\Components\TextInput::make('concession_amount')
                                                            ->label('Special Discount / Concession Amount')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->default(0.00),

                                                        Forms\Components\TextInput::make('concession_reason')
                                                            ->label('Discount Reason / Notes')
                                                            ->placeholder('e.g. Kinship discount, Merit waiver'),
                                                    ]),
                                            ]),

                                        Forms\Components\Section::make('Custom Fee Installments Schedule Builder')
                                            ->description('Define custom installment titles, dates, and amounts for this student. (A late fee of PKR 50/day will apply 10 days after the due date).')
                                            ->schema([
                                                Forms\Components\TextInput::make('custom_installment_count')
                                                    ->label('Number of Installments')
                                                    ->numeric()
                                                    ->default(5),

                                                Forms\Components\Repeater::make('custom_installments')
                                                    ->label('Custom Installment Rows (Optional)')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('title')
                                                            ->label('Installment Title'),
                                                        Forms\Components\TextInput::make('amount')
                                                            ->label('Amount (PKR)')
                                                            ->numeric()
                                                            ->prefix('PKR'),
                                                        Forms\Components\DatePicker::make('due_date')
                                                            ->label('Due Date'),
                                                    ])
                                                    ->columns(3)
                                                    ->collapsible()
                                                    ->cloneable()
                                                    ->reorderable()
                                                    ->defaultItems(0)
                                                    ->columnSpanFull(),
                                            ]),

                                        Forms\Components\Placeholder::make('live_total_preview')
                                            ->label('Live Fee Summary')
                                            ->content(view('filament.admissions.components.fee-summary'))
                                            ->columnSpanFull(),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-fee-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(4, 80),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),

                    // Step 5: Review & Confirm
                    Forms\Components\Wizard\Step::make('Review & Confirm')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        self::getStepIntroPlaceholder(5),
                                        Forms\Components\Placeholder::make('review_summary')
                                            ->label('')
                                            ->content(function (Forms\Get $get) {
                                                $academic = $get('academic_details') ?: [];
                                                $tuition = (float) $get('custom_tuition_fee');
                                                $admissionFee = (float) $get('custom_admission_fee');
                                                $concession = (float) $get('concession_amount');
                                                $total = max(0, $tuition + $admissionFee - $concession);

                                                return view('filament.admissions.components.review-summary', [
                                                    'studentName' => $get('applicant_name') ?: 'Not entered',
                                                    'cnic' => $get('cnic') ?: 'Not entered',
                                                    'dob' => $get('dob') ?: 'Not entered',
                                                    'gender' => ucfirst((string) ($get('gender') ?: 'Male')),
                                                    'guardian' => $get('father_name') ?: 'Not entered',
                                                    'guardianPhone' => $get('father_phone') ?: 'Not entered',
                                                    'qualificationCount' => count($academic),
                                                    'documentCount' => collect(['student_photo', 'student_cnic_front', 'student_cnic_back', 'father_cnic_front', 'father_cnic_back', 'matric_copy', 'inter_copy', 'domicile_copy'])->filter(fn ($k) => filled($get($k)))->count(),
                                                    'course' => self::getAdmissionLookups()['courses'][$get('course_id')] ?? 'Pending selection',
                                                    'campus' => self::getAdmissionLookups()['campuses'][$get('campus_id')] ?? 'Pending selection',
                                                    'session' => self::getAdmissionLookups()['sessions'][$get('academic_session_id')] ?? 'Pending selection',
                                                    'shift' => ucfirst((string) ($get('shift') ?: 'morning')),
                                                    'totalFee' => $total,
                                                    'installments' => (int) ($get('custom_installment_count') ?: 5),
                                                ]);
                                            })
                                            ->columnSpanFull(),
                                    ])->extraAttributes(['class' => 'admission-main-column admission-review-step'])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(5, 100),
                                    ])->extraAttributes(['class' => 'admission-context-column'])->columnSpan(3),
                                ]),
                        ]),
                ])
                // Step changes are local and immediate. The final submit still
                // validates the complete form on the server before saving.
                ->skippable()
                ->nextAction(fn (Forms\Components\Actions\Action $action) => $action
                    ->alpineClickHandler('$event.stopPropagation(); nextStep()'))
                ->extraAlpineAttributes([
                    'x-on:admission-validation-failed.window' => <<<'JS'
                        $nextTick(() => {
                            const invalidField = $el.querySelector('.fi-fo-field-wrp-error-message')
                            const invalidStep = invalidField?.closest('[role="tabpanel"]')

                            if (! invalidStep) {
                                return
                            }

                            step = invalidStep.id
                            autofocusFields()
                            scroll()
                        })
                    JS,
                ])
                // Use the standard Filament form actions in the shared footer.
                ->submitAction('')
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\ImageColumn::make('student_photo')
                    ->label('Photo')
                    ->getStateUsing(fn (Admission $record): ?string => DashboardImage::url($record->student_photo))
                    ->defaultImageUrl(fn (Admission $record): string => DashboardImage::avatar($record->applicant_name))
                    ->circular()
                    ->size(44),
                Tables\Columns\TextColumn::make('applicant_name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cnic')
                    ->label('CNIC / B-Form')
                    ->searchable(),
                Tables\Columns\TextColumn::make('father_name')
                    ->label('Father / Guardian')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Mobile #')
                    ->searchable(),
                Tables\Columns\TextColumn::make('campus.name')
                    ->label('Campus')
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift')
                    ->label('Shift')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'morning' => 'success',
                        'evening' => 'warning',
                        'weekend' => 'info',
                        'offline' => 'gray',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('campus', 'name'),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'name'),
                Tables\Filters\SelectFilter::make('shift')
                    ->options([
                        'morning' => 'Morning',
                        'evening' => 'Evening',
                        'weekend' => 'Weekend',
                        'offline' => 'Offline / Exams',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadAgreement')
                    ->label('Agreement')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn ($record) => route('pdf.admission-agreement', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('downloadZip')
                    ->label('Docs ZIP')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->url(fn ($record) => route('pdf.download-documents-zip', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
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

        if ($user && $user->campus_id !== null) {
            $query->where('campus_id', $user->campus_id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
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

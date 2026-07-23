<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdmissionResource\Pages;
use App\Models\Admission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdmissionResource extends Resource
{
    protected static ?string $model = Admission::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Student Relations';
    protected static ?int $navigationSort = 1;

    protected static function getSidebarPlaceholder(int $stepIndex, int $percentage): Forms\Components\Placeholder
    {
        $stepNames = [
            1 => 'Student Photo',
            2 => 'Student Information',
            3 => 'Parent or Guardian',
            4 => 'Academic Details',
            5 => 'Documents Vault',
            6 => 'Course & Fee Plan',
            7 => 'Review & Confirm',
        ];

        $stepName = $stepNames[$stepIndex] ?? 'Student Information';

        return Forms\Components\Placeholder::make("admission_sidebar_step_{$stepIndex}")
            ->label('')
            ->content(function (Forms\Get $get) use ($stepIndex, $percentage, $stepName) {
                $name = $get('applicant_name') ?: 'Ahmad Hassan';
                $courseId = $get('course_id');
                $course = $courseId ? (\App\Models\Course::find($courseId)?->name ?: 'F.Sc. Pre-Engineering') : 'LHV (Lady Health Visitor)';
                $campusId = $get('campus_id');
                $campus = $campusId ? (\App\Models\Campus::find($campusId)?->name ?: 'Daniyal College Okara') : 'Daniyal College Okara';
                $sessionId = $get('academic_session_id');
                $session = $sessionId ? (\App\Models\AcademicSession::find($sessionId)?->name ?: '2026 - 2028') : '2026 - 2028';
                
                $completedText = "Step " . $stepIndex . " of 7 Active";

                return new \Illuminate\Support\HtmlString("
                    <div class='space-y-4'>
                        <!-- Progress Overview -->
                        <div class='p-5 bg-white border border-slate-200 rounded-xl shadow-sm'>
                            <div class='flex items-center gap-2 mb-2'>
                                <span class='text-emerald-600 font-bold'>🛡️</span>
                                <span class='text-sm font-bold text-slate-800'>Progress Overview</span>
                            </div>
                            <div class='text-xs text-slate-500 mb-2'>{$completedText}</div>
                            <div class='w-full bg-slate-100 rounded-full h-2.5 mb-1'>
                                <div class='bg-[#C9963C] h-2.5 rounded-full' style='width: {$percentage}%'></div>
                            </div>
                            <div class='text-right text-xs font-bold text-[#C9963C]'>{$percentage}%</div>
                        </div>
                        
                        <!-- Admission Summary -->
                        <div class='p-5 bg-white border border-slate-200 rounded-xl shadow-sm'>
                            <div class='text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-3'>Admission Summary</div>
                            <div class='space-y-3'>
                                <div class='flex items-start gap-3'>
                                    <div class='p-2 bg-slate-50 rounded-lg text-slate-500 text-xs'>👤</div>
                                    <div>
                                        <div class='text-[10px] text-slate-400 font-semibold uppercase'>Student Name</div>
                                        <div class='text-xs font-bold text-slate-700'>{$name}</div>
                                    </div>
                                </div>
                                <div class='flex items-start gap-3'>
                                    <div class='p-2 bg-slate-50 rounded-lg text-slate-500 text-xs'>🎓</div>
                                    <div>
                                        <div class='text-[10px] text-slate-400 font-semibold uppercase'>Course</div>
                                        <div class='text-xs font-bold text-slate-700'>{$course}</div>
                                    </div>
                                </div>
                                <div class='flex items-start gap-3'>
                                    <div class='p-2 bg-slate-50 rounded-lg text-slate-500 text-xs'>🏢</div>
                                    <div>
                                        <div class='text-[10px] text-slate-400 font-semibold uppercase'>Campus</div>
                                        <div class='text-xs font-bold text-slate-700'>{$campus}</div>
                                    </div>
                                </div>
                                <div class='flex items-start gap-3'>
                                    <div class='p-2 bg-slate-50 rounded-lg text-slate-500 text-xs'>📅</div>
                                    <div>
                                        <div class='text-[10px] text-slate-400 font-semibold uppercase'>Session</div>
                                        <div class='text-xs font-bold text-slate-700'>{$session}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Tips -->
                        <div class='p-5 bg-amber-50 border border-amber-200 rounded-xl shadow-sm'>
                            <div class='flex items-start gap-2.5'>
                                <div class='text-[#C9963C] text-lg'>💡</div>
                                <div>
                                    <div class='text-xs font-bold text-amber-800'>Quick Tips</div>
                                    <div class='text-[11px] text-amber-700 leading-normal mt-1'>
                                        Double-check CNIC/B-Form and Date of Birth. These details must match official records.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ");
            });
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
                                        Forms\Components\FileUpload::make('student_photo')
                                            ->label('Student Profile Photo')
                                            ->directory('student-photos')
                                            ->image()
                                            ->avatar()
                                            ->imageEditor()
                                            ->circleCropper()
                                            ->helperText('Please upload a student profile picture with a white or blue background, as per the college\'s requirement.')
                                            ->required()
                                            ->columnSpanFull(),
                                    ])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(1, 14),
                                    ])->columnSpan(3),
                                ])
                        ]),

                    // Step 2: Student Information
                    Forms\Components\Wizard\Step::make('Student Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        Forms\Components\Section::make('Personal Identity')
                                            ->schema([
                                                Forms\Components\TextInput::make('applicant_name')
                                                    ->label('Student Full Name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('cnic')
                                                    ->label('Student CNIC or B-Form #')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live()
                                                    ->afterStateUpdated(function ($state) {
                                                        if (empty($state)) return;
                                                        if (\App\Models\Admission::where('cnic', $state)->exists()) {
                                                            \Filament\Notifications\Notification::make()
                                                                ->title('Warning: Possible Duplicate')
                                                                ->body("An admission application with CNIC/B-Form {$state} already exists.")
                                                                ->warning()
                                                                ->send();
                                                        }
                                                    }),
                                                Forms\Components\DatePicker::make('dob')
                                                    ->label('Date of Birth')
                                                    ->required(),
                                                Forms\Components\Select::make('gender')
                                                    ->label('Gender')
                                                    ->options([
                                                        'male' => 'Male',
                                                        'female' => 'Female',
                                                        'other' => 'Other',
                                                    ])
                                                    ->required(),
                                                Forms\Components\Select::make('blood_group')
                                                    ->label('Blood Group')
                                                    ->options([
                                                        'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-',
                                                        'O+' => 'O+', 'O-' => 'O-', 'AB+' => 'AB+', 'AB-' => 'AB-',
                                                    ]),
                                                Forms\Components\Select::make('nationality')
                                                    ->label('Nationality')
                                                    ->options(['Pakistani' => 'Pakistani'])
                                                    ->default('Pakistani')
                                                    ->dehydrated(false)
                                                    ->required(),
                                                Forms\Components\Select::make('religion')
                                                    ->label('Religion')
                                                    ->options([
                                                        'Islam' => 'Islam', 'Christianity' => 'Christianity',
                                                        'Hinduism' => 'Hinduism', 'Sikhism' => 'Sikhism', 'Other' => 'Other',
                                                    ])
                                                    ->default('Islam')
                                                    ->dehydrated(false)
                                                    ->required(),
                                                Forms\Components\TextInput::make('caste')
                                                    ->label('Caste (Optional)')
                                                    ->maxLength(255),
                                            ])->columns(4),

                                        Forms\Components\Section::make('Contact Details')
                                            ->schema([
                                                Forms\Components\TextInput::make('phone')
                                                    ->label('Mobile Number')
                                                    ->tel()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state) {
                                                        if (empty($state)) return;
                                                        if (\App\Models\Admission::where('phone', $state)->exists()) {
                                                            \Filament\Notifications\Notification::make()
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
                                                    ->default('Okara')
                                                    ->required(),
                                                Forms\Components\TextInput::make('domicile_district')
                                                    ->label('Domicile (District)')
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('address')
                                                    ->label('Current Address')
                                                    ->required()
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
                                                    ->default('morning')
                                                    ->required(),
                                                Forms\Components\Select::make('campus_id')
                                                    ->relationship('campus', 'name')
                                                    ->label('Preferred Campus')
                                                    ->required()
                                                    ->default(fn () => filament()->auth()->user()->campus_id)
                                                    ->disabled(fn () => !filament()->auth()->user()->hasRole('Super Admin'))
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
                                                    ->label('Session')
                                                    ->required(),
                                            ])->columns(4),
                                    ])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(2, 28),
                                    ])->columnSpan(3),
                                ])
                        ]),

                    // Step 3: Parent or Guardian
                    Forms\Components\Wizard\Step::make('Parent or Guardian')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        Forms\Components\Section::make('Parent & Guardian Information')
                                            ->schema([
                                                Forms\Components\TextInput::make('father_name')
                                                    ->label("Father's or Guardian's Name")
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('father_cnic')
                                                    ->label("Father/Guardian CNIC #")
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('father_phone')
                                                    ->label("Father's Mobile Number")
                                                    ->tel()
                                                    ->required(),
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
                                                    ->tel()
                                                    ->required(),
                                                Forms\Components\Select::make('guardian_relation')
                                                    ->label('Relationship to Student')
                                                    ->options([
                                                        'Father' => 'Father',
                                                        'Mother' => 'Mother',
                                                        'Guardian' => 'Guardian / Other',
                                                    ])
                                                    ->default('Father')
                                                    ->dehydrated(false)
                                                    ->required(),
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
                                                    ->columnSpanFull(),
                                            ])->columns(3),
                                    ])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(3, 42),
                                    ])->columnSpan(3),
                                ])
                        ]),

                    // Step 4: Academic Details
                    Forms\Components\Wizard\Step::make('Academic Details')
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        Forms\Components\Section::make('Academic Qualifications')
                                            ->description('Add academic details of the applicant')
                                            ->schema([
                                                Forms\Components\Repeater::make('academic_details')
                                                    ->label('')
                                                    ->addActionLabel('Add Qualification')
                                                    ->createItemButtonLabel('Add Academic Detail')
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
                                                                    ->required(fn (Forms\Get $get) => in_array($get('level'), ['matric', 'intermediate']))
                                                                    ->visible(fn (Forms\Get $get) => in_array($get('level'), ['matric', 'intermediate']))
                                                                    ->placeholder('Select degree option'),

                                                                Forms\Components\TextInput::make('degree_title_custom')
                                                                    ->label('Degree Title')
                                                                    ->required()
                                                                    ->visible(fn (Forms\Get $get) => $get('level') === 'graduation')
                                                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('degree_title', $state))
                                                                    ->placeholder('e.g. BS Computer Science'),

                                                                Forms\Components\TextInput::make('board_university')
                                                                    ->label('Board / University')
                                                                    ->required(),

                                                                Forms\Components\TextInput::make('passing_year')
                                                                    ->label('Passing Year')
                                                                    ->required(),

                                                                Forms\Components\TextInput::make('roll_no')
                                                                    ->label('Roll Number')
                                                                    ->required(),

                                                                Forms\Components\TextInput::make('obtained_marks')
                                                                    ->label('Obtained Marks')
                                                                    ->numeric()
                                                                    ->required()
                                                                    ->live(),

                                                                Forms\Components\TextInput::make('total_marks')
                                                                    ->label('Total Marks')
                                                                    ->numeric()
                                                                    ->default(1100)
                                                                    ->required()
                                                                    ->live(),

                                                                Forms\Components\TextInput::make('grade')
                                                                    ->label('Division / Grade')
                                                                    ->placeholder('e.g. A+ or First'),

                                                                Forms\Components\TextInput::make('biology_marks')
                                                                    ->label('Biology Marks')
                                                                    ->numeric()
                                                                    ->required(fn (Forms\Get $get) => $get('level') === 'matric')
                                                                    ->visible(fn (Forms\Get $get) => in_array($get('level'), ['matric', 'intermediate']))
                                                                    ->placeholder('Enter biology marks'),
                                                            ])
                                                            ->visible(fn (Forms\Get $get) => $get('level') !== null),
                                                    ])
                                                    ->columnSpanFull()
                                                    ->grid(1)
                                                    ->default([]),
                                            ]),
                                    ])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(4, 57),
                                    ])->columnSpan(3),
                                ])
                        ]),

                    // Step 5: Documents Vault
                    Forms\Components\Wizard\Step::make('Documents Vault')
                        ->icon('heroicon-o-document-duplicate')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        Forms\Components\Section::make('Upload Credentials')
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\FileUpload::make('cnic_copy')
                                                            ->label('Student CNIC / B-Form Copy')
                                                            ->directory('student-docs')
                                                            ->downloadable()
                                                            ->openable()
                                                            ->placeholder('Drag & drop or Click to upload student CNIC copy'),
                                                        Forms\Components\Select::make('cnic_copy_status')
                                                            ->label('CNIC Status')
                                                            ->options([
                                                                'uploaded' => 'Uploaded',
                                                                'pending' => 'Pending',
                                                                'not_required' => 'Not Required',
                                                                'verified' => 'Verified',
                                                                'rejected' => 'Rejected',
                                                            ])
                                                            ->default('pending'),

                                                        Forms\Components\FileUpload::make('father_cnic_copy')
                                                            ->label('Father / Guardian CNIC Copy')
                                                            ->directory('student-docs')
                                                            ->downloadable()
                                                            ->openable()
                                                            ->placeholder('Drag & drop or Click to upload father CNIC copy'),
                                                        Forms\Components\Select::make('father_cnic_copy_status')
                                                            ->label('Father CNIC Status')
                                                            ->options([
                                                                'uploaded' => 'Uploaded',
                                                                'pending' => 'Pending',
                                                                'not_required' => 'Not Required',
                                                                'verified' => 'Verified',
                                                                'rejected' => 'Rejected',
                                                            ])
                                                            ->default('pending'),

                                                        Forms\Components\FileUpload::make('matric_copy')
                                                            ->label('Matric Result Card / Certificate Copy')
                                                            ->directory('student-docs')
                                                            ->downloadable()
                                                            ->openable()
                                                            ->placeholder('Drag & drop or Click to upload matric certificate'),
                                                        Forms\Components\Select::make('matric_copy_status')
                                                            ->label('Matric Doc Status')
                                                            ->options([
                                                                'uploaded' => 'Uploaded',
                                                                'pending' => 'Pending',
                                                                'not_required' => 'Not Required',
                                                                'verified' => 'Verified',
                                                                'rejected' => 'Rejected',
                                                            ])
                                                            ->default('pending'),

                                                        Forms\Components\FileUpload::make('inter_copy')
                                                            ->label('Intermediate Certificate Copy')
                                                            ->directory('student-docs')
                                                            ->downloadable()
                                                            ->openable()
                                                            ->placeholder('Drag & drop or Click to upload intermediate certificate'),
                                                        Forms\Components\Select::make('inter_copy_status')
                                                            ->label('Inter Doc Status')
                                                            ->options([
                                                                'uploaded' => 'Uploaded',
                                                                'pending' => 'Pending',
                                                                'not_required' => 'Not Required',
                                                                'verified' => 'Verified',
                                                                'rejected' => 'Rejected',
                                                            ])
                                                            ->default('pending'),

                                                        Forms\Components\FileUpload::make('domicile_copy')
                                                            ->label('Domicile Certificate Copy')
                                                            ->directory('student-docs')
                                                            ->downloadable()
                                                            ->openable()
                                                            ->placeholder('Drag & drop or Click to upload domicile copy'),
                                                        Forms\Components\Select::make('domicile_copy_status')
                                                            ->label('Domicile Status')
                                                            ->options([
                                                                'uploaded' => 'Uploaded',
                                                                'pending' => 'Pending',
                                                                'not_required' => 'Not Required',
                                                                'verified' => 'Verified',
                                                                'rejected' => 'Rejected',
                                                            ])
                                                            ->default('pending'),

                                                        Forms\Components\FileUpload::make('character_certificate_copy')
                                                            ->label('Character Certificate Copy')
                                                            ->directory('student-docs')
                                                            ->downloadable()
                                                            ->openable()
                                                            ->placeholder('Drag & drop or Click to upload character certificate'),
                                                        Forms\Components\Select::make('character_certificate_copy_status')
                                                            ->label('Character Cert Status')
                                                            ->options([
                                                                'uploaded' => 'Uploaded',
                                                                'pending' => 'Pending',
                                                                'not_required' => 'Not Required',
                                                                'verified' => 'Verified',
                                                                'rejected' => 'Rejected',
                                                            ])
                                                            ->default('pending'),
                                                    ]),

                                                Forms\Components\Textarea::make('missing_documents')
                                                    ->label('Missing Documents Notes')
                                                    ->placeholder('Enter any missing documents notes here...')
                                                    ->rows(3)
                                                    ->columnSpanFull(),
                                            ]),
                                    ])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(5, 71),
                                    ])->columnSpan(3),
                                ])
                        ]),

                    // Step 6: Course and Fee Plan
                    Forms\Components\Wizard\Step::make('Course & Fee Plan')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        Forms\Components\Section::make('Course Assignment')
                                            ->schema([
                                                Forms\Components\Grid::make(4)
                                                    ->schema([
                                                        Forms\Components\Select::make('campus_id')
                                                            ->relationship('campus', 'name')
                                                            ->required()
                                                            ->default(fn () => filament()->auth()->user()->campus_id)
                                                            ->disabled(fn () => !filament()->auth()->user()->hasRole('Super Admin'))
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
                                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                                 if (!$state) return;
                                                                 $course = \App\Models\Course::find($state);
                                                                 if (!$course) return;

                                                                 $structure = \App\Models\FeeStructure::where('course_id', $state)->first();
                                                                 if ($structure) {
                                                                     $set('custom_tuition_fee', $structure->total_fee);
                                                                     $set('custom_installment_count', $structure->installment_count ?: 12);
                                                                 } else {
                                                                     $set('custom_tuition_fee', 0.00);
                                                                     $set('custom_installment_count', 12);
                                                                 }

                                                                 // Fetch course-specific fee heads
                                                                 $admissionHead = \App\Models\FeeHead::where('course_id', $state)->where('category', 'admission')->first();
                                                                 $set('custom_admission_fee', $admissionHead?->default_amount ?: 0.00);

                                                                 $verificationHead = \App\Models\FeeHead::where('course_id', $state)->where('code', 'like', 'VERIFICATION_%')->first();
                                                                 $set('custom_verification_fee', $verificationHead?->default_amount ?: 0.00);

                                                                 $endowmentHead = \App\Models\FeeHead::where('course_id', $state)->where('category', 'affiliation')->first();
                                                                 $set('custom_enrollment_fee', $endowmentHead?->default_amount ?: 0.00);

                                                                 $examHead = \App\Models\FeeHead::where('course_id', $state)->where('code', 'like', 'EXAM_%')->first();
                                                                 $set('custom_examination_fee', $examHead?->default_amount ?: 0.00);

                                                                 $miscHead = \App\Models\FeeHead::where('course_id', $state)->where('category', 'miscellaneous')->first();
                                                                 $hostelHead = \App\Models\FeeHead::where('course_id', $state)->where('category', 'hostel')->first();
                                                                 $totalMisc = ($miscHead?->default_amount ?: 0.00) + ($hostelHead?->default_amount ?: 0.00);
                                                                 $set('custom_other_misc', $totalMisc);
                                                            }),
                                                        Forms\Components\DatePicker::make('admission_date')
                                                            ->label('Admission Date')
                                                            ->default(now())
                                                            ->required(),
                                                    ])
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
                                                            ->required(),
                                                        Forms\Components\TextInput::make('custom_verification_fee')
                                                            ->label('Verification Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('custom_enrollment_fee')
                                                            ->label('Enrollment Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->required(),
                                                    ]),

                                                Forms\Components\Section::make('Recurring Tuition')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('custom_tuition_fee')
                                                            ->label('Tuition Fee Total')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
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
                                                                return new \Illuminate\Support\HtmlString("<div class='p-3 bg-amber-50 rounded-lg text-center font-bold text-amber-700'>PKR " . number_format($perInstallment, 2) . "</div>");
                                                            }),
                                                    ]),

                                                Forms\Components\Section::make('Additional Charges')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('custom_examination_fee')
                                                            ->label('Examination Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('custom_other_misc')
                                                            ->label('Other / Miscellaneous Fee')
                                                            ->numeric()
                                                            ->prefix('PKR')
                                                            ->live()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('reference')
                                                            ->label('Other Charge Description')
                                                            ->placeholder('e.g. ID Card, Library Fee'),
                                                    ]),
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
                                                Forms\Components\TextInput::make('concession_approver')
                                                    ->label('Approving Authority / Officer')
                                                    ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none'),
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
                                                if ($perInstallment < 0) $perInstallment = 0;

                                                return new \Illuminate\Support\HtmlString(sprintf(
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
                                                            <span>Installment Timeline: 1st installment due on 15th of next month</span>
                                                            <button type="button" class="px-3 py-1.5 bg-[#082245] text-white rounded font-bold hover:bg-[#081F35] transition">Preview All Vouchers</button>
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
                                    ])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(6, 85),
                                    ])->columnSpan(3),
                                ])
                        ]),

                    // Step 7: Review and Confirm
                    Forms\Components\Wizard\Step::make('Review & Confirm')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->extraAttributes(['class' => 'admission-split-grid'])
                                ->schema([
                                    Forms\Components\Group::make([
                                        Forms\Components\Placeholder::make('review_summary')
                                            ->label('')
                                            ->content(function (Forms\Get $get) {
                                                $name = $get('applicant_name') ?: 'Ahmad Hassan';
                                                $cnic = $get('cnic') ?: '42201-1234567-1';
                                                $dob = $get('dob') ?: '12/05/2007';
                                                $gender = $get('gender') ?: 'Male';
                                                $phone = $get('phone') ?: '0300 1234567';
                                                $email = $get('email') ?: 'ahmad.hassan@email.com';
                                                $city = $get('city') ?: 'Okara';
                                                $domicile = $get('domicile_district') ?: 'Okara';
                                                
                                                return new \Illuminate\Support\HtmlString("
                                                    <div class='space-y-4'>
                                                        <div class='p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center gap-3 text-emerald-800 text-sm'>
                                                            <span class='text-lg'>✅</span>
                                                            <div>
                                                                <div class='font-bold'>Review Summary: All steps completed successfully!</div>
                                                                <div class='text-xs opacity-90'>You are about to submit the admission for {$name}.</div>
                                                            </div>
                                                        </div>

                                                        <div class='p-5 bg-white border border-slate-200 rounded-xl space-y-4 shadow-sm'>
                                                            <div class='border-b pb-2 font-bold text-slate-800 text-sm flex items-center justify-between'>
                                                                <span>Student Profile</span>
                                                                <span class='px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold'>Completed</span>
                                                            </div>
                                                            <div class='grid grid-cols-2 gap-4 text-xs'>
                                                                <div><span class='text-slate-400 font-semibold'>Student Name:</span> <span class='font-bold text-slate-700'>{$name}</span></div>
                                                                <div><span class='text-slate-400 font-semibold'>CNIC / B-Form #:</span> <span class='font-bold text-slate-700'>{$cnic}</span></div>
                                                                <div><span class='text-slate-400 font-semibold'>Date of Birth:</span> <span class='font-bold text-slate-700'>{$dob}</span></div>
                                                                <div><span class='text-slate-400 font-semibold'>Gender:</span> <span class='font-bold text-slate-700'>{$gender}</span></div>
                                                            </div>
                                                        </div>

                                                        <div class='p-5 bg-white border border-slate-200 rounded-xl space-y-4 shadow-sm'>
                                                            <div class='border-b pb-2 font-bold text-slate-800 text-sm flex items-center justify-between'>
                                                                <span>Contact Information</span>
                                                                <span class='px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold'>Completed</span>
                                                            </div>
                                                            <div class='grid grid-cols-2 gap-4 text-xs'>
                                                                <div><span class='text-slate-400 font-semibold'>Mobile Number:</span> <span class='font-bold text-slate-700'>{$phone}</span></div>
                                                                <div><span class='text-slate-400 font-semibold'>Email:</span> <span class='font-bold text-slate-700'>{$email}</span></div>
                                                                <div><span class='text-slate-400 font-semibold'>City:</span> <span class='font-bold text-slate-700'>{$city}</span></div>
                                                                <div><span class='text-slate-400 font-semibold'>Domicile:</span> <span class='font-bold text-slate-700'>{$domicile}</span></div>
                                                            </div>
                                                        </div>

                                                        <div class='p-5 bg-white border border-slate-200 rounded-xl space-y-4 shadow-sm'>
                                                            <div class='border-b pb-2 font-bold text-slate-800 text-sm flex items-center justify-between'>
                                                                <span>Terms & Declaration</span>
                                                                <span class='px-2.5 py-0.5 bg-amber-100 text-amber-800 rounded-full text-[10px] font-bold'>Pending Declaration</span>
                                                            </div>
                                                            <div class='text-xs text-slate-500 leading-relaxed'>
                                                                Please check the declaration box below confirming that you have reviewed all academic credentials, uploaded document statuses, and final concession fee structures before submitting the application.
                                                            </div>
                                                        </div>
                                                    </div>
                                                ");
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
                                    ])->columnSpan(9),
                                    Forms\Components\Group::make([
                                        self::getSidebarPlaceholder(7, 100),
                                    ])->columnSpan(3),
                                ])
                        ])
                ])->columnSpanFull()
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
                            \App\Services\EnrollmentService::enroll($record, filament()->auth()->id());
                            \Filament\Notifications\Notification::make()
                                ->title('Enrolled Successfully')
                                ->body("Student has been registered and fee ledger vouchers generated.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
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
        
        if (!filament()->auth()->user()->hasRole('Super Admin')) {
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

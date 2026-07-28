<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Models\Staff;
use App\Models\User;
use App\Models\TeacherAcademic;
use App\Models\ProfessionalRegistration;
use App\Models\EmploymentRecord;
use App\Models\SalaryRecord;
use App\Models\StaffDocument;
use App\Models\Course;
use App\Services\HR\GenerateEmployeeIdService;
use App\Services\HR\CalculateProfileCompletionService;
use App\Services\HR\EvaluateAgreementReadinessService;
use App\Services\HR\GenerateTeacherAgreementAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;

class CreateStaffWizard extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use WithFileUploads;

    protected static string $resource = StaffResource::class;
    protected static string $view = 'filament.resources.staff-resource.pages.create-staff-wizard';
    protected static ?string $title = 'Add New Teacher / Staff Member';

    public ?array $data = [];
    public int $currentStep = 1;

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function mount(): void
    {
        $user = filament()->auth()->user();
        $defaultCampusId = $user ? $user->campus_id : null;

        $autoEmpId = GenerateEmployeeIdService::generate($defaultCampusId, 'TEA');

        $this->form->fill([
            'employee_id' => $autoEmpId,
            'campus_id' => $defaultCampusId,
            'staff_category' => 'teaching',
            'employment_type' => 'full_time',
            'appointment_status' => 'probation',
            'joining_date' => now()->format('Y-m-d'),
            'probation_start_date' => now()->format('Y-m-d'),
            'probation_end_date' => now()->addMonths(6)->format('Y-m-d'),
            'currency' => 'PKR',
            'payment_method' => 'bank',
            'weekly_working_hours' => 40,
            'weekly_teaching_hours' => 20,
            'employee_notice_days' => 30,
            'college_notice_days' => 30,
        ]);
    }

    public function form(Form $form): Form
    {
        $user = filament()->auth()->user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');

        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    // STEP 1: Personal Information
                    Forms\Components\Wizard\Step::make('1. Personal')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\Section::make('Teacher Identity & Campus Posting')
                                ->schema([
                                    Forms\Components\Select::make('campus_id')
                                        ->label('Hiring / Assigned Campus')
                                        ->options(\App\Models\Campus::pluck('name', 'id'))
                                        ->required()
                                        ->searchable()
                                        ->disabled(!$isSuperAdmin)
                                        ->dehydrated()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                            $set('employee_id', GenerateEmployeeIdService::generate($state, $get('staff_category') ?? 'TEA'));
                                        }),
                                    Forms\Components\TextInput::make('employee_id')
                                        ->label('Employee ID (Auto-Generated)')
                                        ->disabled()
                                        ->dehydrated()
                                        ->required(),
                                    Forms\Components\FileUpload::make('photo_path')
                                        ->label('Profile Photograph')
                                        ->image()
                                        ->directory('staff/photos')
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('full_name')
                                        ->label('Full Name')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('father_or_spouse_name')
                                        ->label('Father / Spouse Name')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('cnic')
                                        ->label('CNIC Number')
                                        ->placeholder('38403-1234567-1')
                                        ->required()
                                        ->unique('staff', 'cnic')
                                        ->maxLength(20),
                                    Forms\Components\DatePicker::make('cnic_issue_date')
                                        ->label('CNIC Issue Date'),
                                    Forms\Components\DatePicker::make('cnic_expiry_date')
                                        ->label('CNIC Expiry Date'),
                                    Forms\Components\DatePicker::make('date_of_birth')
                                        ->label('Date of Birth'),
                                    Forms\Components\Select::make('gender')
                                        ->options([
                                            'male' => 'Male',
                                            'female' => 'Female',
                                            'other' => 'Other',
                                        ]),
                                    Forms\Components\Select::make('marital_status')
                                        ->options([
                                            'single' => 'Single',
                                            'married' => 'Married',
                                            'divorced' => 'Divorced',
                                            'widowed' => 'Widowed',
                                        ]),
                                ])->columns(3),

                            Forms\Components\Section::make('Contact Details')
                                ->schema([
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Primary Mobile Number')
                                        ->tel()
                                        ->required(),
                                    Forms\Components\TextInput::make('whatsapp')
                                        ->label('WhatsApp Number'),
                                    Forms\Components\TextInput::make('email')
                                        ->label('Email Address')
                                        ->email(),
                                    Forms\Components\Textarea::make('current_address')
                                        ->label('Current Residential Address')
                                        ->columnSpanFull(),
                                    Forms\Components\Textarea::make('permanent_address')
                                        ->label('Permanent Address')
                                        ->columnSpanFull(),
                                ])->columns(3),

                            Forms\Components\Section::make('Emergency Contact')
                                ->schema([
                                    Forms\Components\TextInput::make('emergency_contact_name')
                                        ->label('Contact Name')
                                        ->required(),
                                    Forms\Components\TextInput::make('emergency_contact_relationship')
                                        ->label('Relationship')
                                        ->required(),
                                    Forms\Components\TextInput::make('emergency_contact_phone')
                                        ->label('Emergency Phone Number')
                                        ->required(),
                                ])->columns(3),
                        ]),

                    // STEP 2: Academic & Professional
                    Forms\Components\Wizard\Step::make('2. Academic')
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Forms\Components\Section::make('Qualifications & Experience')
                                ->schema([
                                    Forms\Components\Select::make('highest_qualification')
                                        ->options([
                                            'PhD' => 'Doctorate (PhD)',
                                            'MPhil' => 'M.Phil / MS',
                                            'Master' => 'Master Degree',
                                            'Bachelor' => 'Bachelor Degree',
                                            'Diploma' => 'Diploma / Certification',
                                        ]),
                                    Forms\Components\TextInput::make('degree_title')
                                        ->label('Degree Title (e.g. Pharm-D, M.Phil Pharmacy)'),
                                    Forms\Components\TextInput::make('specialization')
                                        ->label('Specialization Area'),
                                    Forms\Components\TextInput::make('institution')
                                        ->label('University / Institute'),
                                    Forms\Components\TextInput::make('passing_year')
                                        ->numeric(),
                                    Forms\Components\TextInput::make('teaching_experience_years')
                                        ->label('Teaching Experience (Years)')
                                        ->numeric()
                                        ->default(0),
                                    Forms\Components\TextInput::make('clinical_experience_years')
                                        ->label('Clinical / Industry Exp. (Years)')
                                        ->numeric()
                                        ->default(0),
                                    Forms\Components\TextInput::make('previous_employer')
                                        ->label('Previous Employer'),
                                    Forms\Components\TextInput::make('previous_designation')
                                        ->label('Previous Designation'),
                                    Forms\Components\Textarea::make('professional_summary')
                                        ->columnSpanFull(),
                                ])->columns(3),

                            Forms\Components\Section::make('Professional Registration')
                                ->schema([
                                    Forms\Components\TextInput::make('registration_body')
                                        ->label('Registration Body (e.g. Pharmacy Council, PNC)'),
                                    Forms\Components\TextInput::make('registration_number')
                                        ->label('Licence / Registration Number'),
                                    Forms\Components\DatePicker::make('reg_issue_date')
                                        ->label('Issue Date'),
                                    Forms\Components\DatePicker::make('reg_expiry_date')
                                        ->label('Expiry Date'),
                                ])->columns(2),

                            Forms\Components\Section::make('Document Uploads')
                                ->schema([
                                    Forms\Components\FileUpload::make('document_cnic')
                                        ->label('CNIC Copy (PDF/Image)')
                                        ->directory('staff/documents/cnic'),
                                    Forms\Components\FileUpload::make('document_degree')
                                        ->label('Degree Certificate (PDF/Image)')
                                        ->directory('staff/documents/degrees'),
                                    Forms\Components\FileUpload::make('document_cv')
                                        ->label('CV / Resume')
                                        ->directory('staff/documents/cv'),
                                    Forms\Components\FileUpload::make('document_experience')
                                        ->label('Experience Letters')
                                        ->directory('staff/documents/experience'),
                                ])->columns(2),
                        ]),

                    // STEP 3: Employment & Posting
                    Forms\Components\Wizard\Step::make('3. Employment')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Forms\Components\Section::make('Posting Details')
                                ->schema([
                                    Forms\Components\Select::make('campus_id')
                                        ->options(\App\Models\Campus::pluck('name', 'id'))
                                        ->required()
                                        ->disabled(!$isSuperAdmin)
                                        ->dehydrated()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                            $set('employee_id', GenerateEmployeeIdService::generate($state, $get('staff_category') ?? 'TEA'));
                                        }),
                                    Forms\Components\Select::make('staff_category')
                                        ->options([
                                            'teaching' => 'Teaching Staff',
                                            'administrative' => 'Administrative Staff',
                                            'support' => 'Support Staff',
                                        ])
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                            $set('employee_id', GenerateEmployeeIdService::generate($get('campus_id'), $state ?? 'TEA'));
                                        }),
                                    Forms\Components\TextInput::make('department')
                                        ->placeholder('e.g. Pharmacy, Allied Health'),
                                    Forms\Components\Select::make('programme_id')
                                        ->label('Associated Programme / Course')
                                        ->options(Course::pluck('name', 'id')),
                                    Forms\Components\Select::make('designation')
                                        ->options([
                                            'Principal' => 'Principal',
                                            'Vice Principal' => 'Vice Principal',
                                            'Head of Department' => 'Head of Department',
                                            'Professor' => 'Professor',
                                            'Associate Professor' => 'Associate Professor',
                                            'Assistant Professor' => 'Assistant Professor',
                                            'Senior Lecturer' => 'Senior Lecturer',
                                            'Lecturer' => 'Lecturer',
                                            'Clinical Instructor' => 'Clinical Instructor',
                                            'Demonstrator' => 'Demonstrator',
                                            'Visiting Lecturer' => 'Visiting Lecturer',
                                            'Lab Instructor' => 'Lab Instructor',
                                        ])
                                        ->required()
                                        ->searchable(),
                                    Forms\Components\Select::make('reporting_officer_id')
                                        ->label('Reporting Officer')
                                        ->options(User::pluck('name', 'id'))
                                        ->searchable(),
                                    Forms\Components\Select::make('employment_type')
                                        ->options([
                                            'full_time' => 'Full-time',
                                            'part_time' => 'Part-time',
                                            'visiting' => 'Visiting',
                                            'contract' => 'Contract',
                                        ])
                                        ->required(),
                                    Forms\Components\Select::make('appointment_status')
                                        ->options([
                                            'probation' => 'Probation',
                                            'permanent' => 'Permanent',
                                            'contract' => 'Fixed-Term Contract',
                                        ])
                                        ->required()
                                        ->reactive(),
                                    Forms\Components\DatePicker::make('joining_date')
                                        ->required(),
                                    Forms\Components\Select::make('shift')
                                        ->options([
                                            'Morning' => 'Morning Shift',
                                            'Evening' => 'Evening Shift',
                                        ]),
                                    Forms\Components\TextInput::make('biometric_id')
                                        ->label('Biometric / Device ID'),
                                ])->columns(3),

                            Forms\Components\Section::make('Appointment Status Details')
                                ->schema([
                                    Forms\Components\DatePicker::make('probation_start_date')
                                        ->visible(fn (Forms\Get $get) => $get('appointment_status') === 'probation'),
                                    Forms\Components\DatePicker::make('probation_end_date')
                                        ->visible(fn (Forms\Get $get) => $get('appointment_status') === 'probation'),
                                    Forms\Components\DatePicker::make('confirmation_date')
                                        ->visible(fn (Forms\Get $get) => $get('appointment_status') === 'permanent'),
                                    Forms\Components\DatePicker::make('contract_start_date')
                                        ->visible(fn (Forms\Get $get) => $get('appointment_status') === 'contract'),
                                    Forms\Components\DatePicker::make('contract_end_date')
                                        ->visible(fn (Forms\Get $get) => $get('appointment_status') === 'contract'),
                                ])->columns(2),
                        ]),

                    // STEP 4: Payroll
                    Forms\Components\Wizard\Step::make('4. Payroll')
                        ->icon('heroicon-o-currency-dollar')
                        ->visible($isSuperAdmin)
                        ->schema([
                            Forms\Components\Section::make('Monthly Remuneration')
                                ->schema([
                                    Forms\Components\TextInput::make('basic_salary')
                                        ->numeric()
                                        ->prefix('PKR')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                            $set('gross_salary', (float)$state + (float)$get('house_allowance') + (float)$get('transport_allowance') + (float)$get('medical_allowance') + (float)$get('other_allowance'))
                                        ),
                                    Forms\Components\TextInput::make('probation_salary')
                                        ->numeric()
                                        ->prefix('PKR'),
                                    Forms\Components\TextInput::make('house_allowance')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('PKR')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                            $set('gross_salary', (float)$get('basic_salary') + (float)$state + (float)$get('transport_allowance') + (float)$get('medical_allowance') + (float)$get('other_allowance'))
                                        ),
                                    Forms\Components\TextInput::make('transport_allowance')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('PKR')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                            $set('gross_salary', (float)$get('basic_salary') + (float)$get('house_allowance') + (float)$state + (float)$get('medical_allowance') + (float)$get('other_allowance'))
                                        ),
                                    Forms\Components\TextInput::make('medical_allowance')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('PKR')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                            $set('gross_salary', (float)$get('basic_salary') + (float)$get('house_allowance') + (float)$get('transport_allowance') + (float)$state + (float)$get('other_allowance'))
                                        ),
                                    Forms\Components\TextInput::make('other_allowance')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('PKR')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                            $set('gross_salary', (float)$get('basic_salary') + (float)$get('house_allowance') + (float)$get('transport_allowance') + (float)$get('medical_allowance') + (float)$state)
                                        ),
                                    Forms\Components\TextInput::make('gross_salary')
                                        ->numeric()
                                        ->prefix('PKR')
                                        ->required(),
                                ])->columns(3),

                            Forms\Components\Section::make('Deductions & Bank Account')
                                ->schema([
                                    Forms\Components\TextInput::make('recurring_deduction')->numeric()->default(0)->prefix('PKR'),
                                    Forms\Components\TextInput::make('tax_deduction')->numeric()->default(0)->prefix('PKR'),
                                    Forms\Components\TextInput::make('bank_name')->placeholder('e.g. Meezan Bank, HBL'),
                                    Forms\Components\TextInput::make('bank_branch'),
                                    Forms\Components\TextInput::make('account_title'),
                                    Forms\Components\TextInput::make('account_number'),
                                    Forms\Components\TextInput::make('iban')->label('IBAN Number'),
                                ])->columns(3),
                        ]),

                    // STEP 5: Review & Confirmation
                    Forms\Components\Wizard\Step::make('5. Review')
                        ->icon('heroicon-o-check-badge')
                        ->schema([
                            Forms\Components\Placeholder::make('review_summary')
                                ->label('Onboarding Summary')
                                ->content('Please review the summary in the right-side panel. Click "Complete Onboarding" to save the teacher profile, or "Save Draft" to keep progress as a draft.'),
                        ]),
                ])
                ->submitAction(new \Illuminate\Support\HtmlString('<button type="submit" class="fi-btn fi-btn-size-md fi-btn-color-primary px-6 py-2 bg-navy-900 text-white font-bold rounded-lg hover:bg-navy-800">Complete Onboarding</button>'))
            ])
            ->statePath('data');
    }

    public function saveDraft(): void
    {
        $this->saveRecord('draft');
    }

    public function saveAndComplete(): void
    {
        $this->saveRecord('active');
    }

    protected function saveRecord(string $status): void
    {
        $state = $this->form->getState();

        DB::transaction(function () use ($state, $status) {
            // 1. Create or Find User account if email provided
            $user = null;
            if (!empty($state['email'])) {
                $user = User::where('email', $state['email'])->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $state['full_name'],
                        'email' => $state['email'],
                        'password' => Hash::make('password123'),
                        'phone' => $state['phone'] ?? null,
                        'campus_id' => $state['campus_id'] ?? null,
                        'status' => true,
                    ]);
                    $user->assignRole('Faculty');
                }
            }

            // 2. Create Staff record
            $staff = Staff::create([
                'user_id' => $user ? $user->id : null,
                'campus_id' => $state['campus_id'],
                'employee_id' => $state['employee_id'],
                'full_name' => $state['full_name'],
                'father_or_spouse_name' => $state['father_or_spouse_name'] ?? null,
                'cnic' => $state['cnic'],
                'cnic_issue_date' => $state['cnic_issue_date'] ?? null,
                'cnic_expiry_date' => $state['cnic_expiry_date'] ?? null,
                'date_of_birth' => $state['date_of_birth'] ?? null,
                'gender' => $state['gender'] ?? null,
                'marital_status' => $state['marital_status'] ?? null,
                'phone' => $state['phone'],
                'whatsapp' => $state['whatsapp'] ?? null,
                'current_address' => $state['current_address'] ?? null,
                'permanent_address' => $state['permanent_address'] ?? null,
                'emergency_contact_name' => $state['emergency_contact_name'],
                'emergency_contact_relationship' => $state['emergency_contact_relationship'],
                'emergency_contact_phone' => $state['emergency_contact_phone'],
                'photo_path' => $state['photo_path'] ?? null,
                'designation' => $state['designation'],
                'department' => $state['department'] ?? null,
                'hire_date' => $state['joining_date'],
                'joining_date' => $state['joining_date'],
                'staff_category' => $state['staff_category'] ?? 'teaching',
                'record_status' => $status,
                'is_active' => ($status === 'active'),
            ]);

            // 3. Create Academic Record
            if (!empty($state['highest_qualification']) || !empty($state['degree_title'])) {
                TeacherAcademic::create([
                    'staff_id' => $staff->id,
                    'highest_qualification' => $state['highest_qualification'] ?? null,
                    'degree_title' => $state['degree_title'] ?? null,
                    'specialization' => $state['specialization'] ?? null,
                    'institution' => $state['institution'] ?? null,
                    'passing_year' => $state['passing_year'] ?? null,
                    'teaching_experience_years' => $state['teaching_experience_years'] ?? 0,
                    'clinical_experience_years' => $state['clinical_experience_years'] ?? 0,
                    'previous_employer' => $state['previous_employer'] ?? null,
                    'previous_designation' => $state['previous_designation'] ?? null,
                    'professional_summary' => $state['professional_summary'] ?? null,
                ]);
            }

            // 4. Create Registration
            if (!empty($state['registration_number'])) {
                ProfessionalRegistration::create([
                    'staff_id' => $staff->id,
                    'registration_body' => $state['registration_body'] ?? 'Pharmacy Council',
                    'registration_number' => $state['registration_number'],
                    'issue_date' => $state['reg_issue_date'] ?? null,
                    'expiry_date' => $state['reg_expiry_date'] ?? null,
                    'status' => 'verified',
                ]);
            }

            // 5. Create Employment Record
            EmploymentRecord::create([
                'staff_id' => $staff->id,
                'campus_id' => $state['campus_id'],
                'department' => $state['department'] ?? null,
                'programme_id' => $state['programme_id'] ?? null,
                'designation' => $state['designation'],
                'reporting_officer_id' => $state['reporting_officer_id'] ?? null,
                'employment_type' => $state['employment_type'] ?? 'full_time',
                'appointment_status' => $state['appointment_status'] ?? 'probation',
                'joining_date' => $state['joining_date'],
                'probation_start_date' => $state['probation_start_date'] ?? null,
                'probation_end_date' => $state['probation_end_date'] ?? null,
                'confirmation_date' => $state['confirmation_date'] ?? null,
                'contract_start_date' => $state['contract_start_date'] ?? null,
                'contract_end_date' => $state['contract_end_date'] ?? null,
                'shift' => $state['shift'] ?? null,
                'biometric_id' => $state['biometric_id'] ?? null,
                'weekly_working_hours' => $state['weekly_working_hours'] ?? 40,
                'weekly_teaching_hours' => $state['weekly_teaching_hours'] ?? 20,
                'effective_from' => $state['joining_date'],
                'is_current' => true,
                'created_by' => auth()->id(),
            ]);

            // 6. Create Salary Record if provided
            if (isset($state['gross_salary']) && (float)$state['gross_salary'] > 0) {
                SalaryRecord::create([
                    'staff_id' => $staff->id,
                    'currency' => $state['currency'] ?? 'PKR',
                    'basic_salary' => $state['basic_salary'] ?? $state['gross_salary'],
                    'gross_salary' => $state['gross_salary'],
                    'probation_salary' => $state['probation_salary'] ?? null,
                    'house_allowance' => $state['house_allowance'] ?? 0,
                    'transport_allowance' => $state['transport_allowance'] ?? 0,
                    'medical_allowance' => $state['medical_allowance'] ?? 0,
                    'other_allowance' => $state['other_allowance'] ?? 0,
                    'recurring_deduction' => $state['recurring_deduction'] ?? 0,
                    'tax_deduction' => $state['tax_deduction'] ?? 0,
                    'payment_method' => $state['payment_method'] ?? 'bank',
                    'bank_name' => $state['bank_name'] ?? null,
                    'bank_branch' => $state['bank_branch'] ?? null,
                    'account_title' => $state['account_title'] ?? null,
                    'account_number_encrypted' => $state['account_number'] ?? null,
                    'iban_encrypted' => $state['iban'] ?? null,
                    'effective_from' => $state['joining_date'],
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                    'approved_by' => auth()->id(),
                ]);
            }

            // 7. Save Document Uploads
            $docs = [
                'document_cnic' => 'cnic',
                'document_degree' => 'degree',
                'document_cv' => 'cv',
                'document_experience' => 'experience',
            ];

            foreach ($docs as $key => $docType) {
                if (!empty($state[$key])) {
                    StaffDocument::create([
                        'staff_id' => $staff->id,
                        'document_type' => $docType,
                        'title' => strtoupper($docType) . ' Document',
                        'path' => $state[$key],
                        'stored_filename' => basename($state[$key]),
                        'status' => 'verified',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            // 8. Update Completion %
            $comp = CalculateProfileCompletionService::evaluate($staff);
            $staff->update([
                'completion_percentage' => $comp['percentage'],
            ]);

            Notification::make()
                ->title($status === 'draft' ? 'Staff Draft Saved' : 'Teacher Profile Created Successfully')
                ->success()
                ->send();

            $this->redirect(StaffResource::getUrl('index'));
        });
    }

    public function submit(): void
    {
        $this->saveAndComplete();
    }
}

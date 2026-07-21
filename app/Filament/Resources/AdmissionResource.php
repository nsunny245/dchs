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
                    Forms\Components\Wizard\Step::make('Student Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\FileUpload::make('student_photo')
                                ->label('Student Profile Photo')
                                ->directory('student-photos')
                                ->image()
                                ->avatar()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('applicant_name')
                                ->label('Full Name (English)')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('cnic')
                                ->label('Student CNIC / B-Form #')
                                ->required()
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
                                })
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
                                ->label('Email Address')
                                ->email()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('city')
                                ->label('City')
                                ->default('Okara')
                                ->required(),
                            Forms\Components\Select::make('shift')
                                ->label('Shift Preference')
                                ->options([
                                    'morning' => 'Morning',
                                    'evening' => 'Evening',
                                ])
                                ->default('morning')
                                ->required(),
                            Forms\Components\TextInput::make('reference')
                                ->label('Inquiry Source / Reference'),
                            Forms\Components\Select::make('visitor_query_id')
                                ->label('Linked Visitor Inquiry')
                                ->relationship('visitorQuery', 'visitor_name')
                                ->searchable()
                                ->preload()
                                ->nullable(),
                            Forms\Components\Textarea::make('address')
                                ->label('Current Address')
                                ->required()
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Parent or Guardian')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Forms\Components\TextInput::make('father_name')
                                ->label("Father's or Guardian's Name")
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('father_cnic')
                                ->label("Father/Guardian CNIC #")
                                ->maxLength(255),
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
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Academic Eligibility')
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Forms\Components\Placeholder::make('eligibility_status')
                                ->label('Automatic Eligibility Assessment')
                                ->content(function (Forms\Get $get) {
                                    $courseId = $get('course_id');
                                    if (!$courseId) {
                                        return 'Please select the Course/Program in Step 5 first to check eligibility.';
                                    }
                                    $course = \App\Models\Course::find($courseId);
                                    if (!$course) return '';

                                    $obtained = (float)$get('matric_obtained_marks');
                                    $total = (float)$get('matric_total_marks') ?: 1100;
                                    $percentage = $total > 0 ? ($obtained / $total) * 100 : 0;
                                    $biology = (float)$get('matric_biology_marks');

                                    $isEligible = true;
                                    $reasons = [];

                                    if (in_array($course->code, ['LHV', 'CMW', 'CNA', 'PT', 'MLT', 'OT', 'DT', 'AT'])) {
                                        if ($percentage < 45) {
                                            $isEligible = false;
                                            $reasons[] = 'Matric percentage is below 45% (required for Allied Health)';
                                        }
                                        if ($biology <= 0) {
                                            $isEligible = false;
                                            $reasons[] = 'Biology marks are required for science programs';
                                        }
                                    }

                                    if ($isEligible) {
                                        return new \Illuminate\Support\HtmlString('<div class="px-4 py-3 bg-emerald-600 text-white rounded-lg font-bold text-center">✅ Eligible (' . round($percentage, 1) . '%)</div>');
                                    } else {
                                        return new \Illuminate\Support\HtmlString('<div class="px-4 py-3 bg-rose-600 text-white rounded-lg font-bold text-center">❌ Eligibility Issue (' . implode(', ', $reasons) . ')</div>');
                                    }
                                })
                                ->columnSpanFull(),

                            Forms\Components\Section::make('Matriculation / SSC Details')
                                ->schema([
                                    Forms\Components\TextInput::make('matric_degree')
                                        ->label('Degree Title')
                                        ->default('Matric Science')
                                        ->required(),
                                    Forms\Components\TextInput::make('matric_board')
                                        ->label('Board / University')
                                        ->required(),
                                    Forms\Components\TextInput::make('matric_year')
                                        ->label('Passing Year')
                                        ->required(),
                                    Forms\Components\TextInput::make('matric_roll_no')
                                        ->label('Roll Number')
                                        ->required(),
                                    Forms\Components\TextInput::make('matric_obtained_marks')
                                        ->numeric()
                                        ->label('Obtained Marks')
                                        ->live()
                                        ->required(),
                                    Forms\Components\TextInput::make('matric_total_marks')
                                        ->numeric()
                                        ->label('Total Marks')
                                        ->default(1100)
                                        ->live()
                                        ->required(),
                                    Forms\Components\TextInput::make('matric_grade')
                                        ->label('Division / Grade'),
                                    Forms\Components\TextInput::make('matric_biology_marks')
                                        ->numeric()
                                        ->label('Biology Marks')
                                        ->live()
                                        ->required(),
                                ])->columns(2),

                            Forms\Components\Section::make('Intermediate / HSSC Details (Optional)')
                                ->schema([
                                    Forms\Components\TextInput::make('inter_degree')
                                        ->label('Degree Title'),
                                    Forms\Components\TextInput::make('inter_board')
                                        ->label('Board / University'),
                                    Forms\Components\TextInput::make('inter_year')
                                        ->label('Passing Year'),
                                    Forms\Components\TextInput::make('inter_roll_no')
                                        ->label('Roll Number'),
                                    Forms\Components\TextInput::make('inter_obtained_marks')
                                        ->numeric()
                                        ->label('Obtained Marks'),
                                    Forms\Components\TextInput::make('inter_total_marks')
                                        ->numeric()
                                        ->label('Total Marks'),
                                    Forms\Components\TextInput::make('inter_grade')
                                        ->label('Division / Grade'),
                                ])->columns(2),
                        ]),

                    Forms\Components\Wizard\Step::make('Documents Vault')
                        ->icon('heroicon-o-document-duplicate')
                        ->schema([
                            Forms\Components\Section::make('Document Attachments')
                                ->description('Upload student certificate copies individually')
                                ->schema([
                                    Forms\Components\FileUpload::make('cnic_copy')
                                        ->label('Student CNIC / B-Form Copy')
                                        ->directory('student-docs'),
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
                                        ->directory('student-docs'),
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
                                        ->directory('student-docs'),
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
                                        ->directory('student-docs'),
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
                                        ->directory('student-docs'),
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
                                        ->directory('student-docs'),
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
                                ])->columns(2),

                            Forms\Components\Textarea::make('missing_documents')
                                ->label('Missing Documents Notes')
                                ->placeholder('Enter any missing documents here...')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Wizard\Step::make('Course and Fee Plan')
                        ->icon('heroicon-o-currency-dollar')
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
                                ->live(),
                            Forms\Components\DatePicker::make('admission_date')
                                ->label('Admission Date')
                                ->default(now())
                                ->required(),

                            Forms\Components\Placeholder::make('fee_structure_preview')
                                ->label('Official Fee Plan Preview')
                                ->content(function (Forms\Get $get) {
                                    $courseId = $get('course_id');
                                    $campusId = $get('campus_id') ?: filament()->auth()->user()?->campus_id;
                                    if (!$courseId || !$campusId) {
                                        return 'Please select a Course/Program first to load the fee structures.';
                                    }

                                    $structure = \App\Models\FeeStructure::where('course_id', $courseId)
                                        ->where(function ($q) use ($campusId) {
                                            $q->where('campus_id', $campusId)
                                              ->orWhereNull('campus_id')
                                              ->orWhere('campus_id', 0)
                                              ->orWhere('campus_id', '');
                                        })
                                        ->orderByRaw('campus_id IS NOT NULL AND campus_id != 0 AND campus_id != "" DESC')
                                        ->first();

                                    if (!$structure) {
                                        $structure = \App\Models\FeeStructure::where('course_id', $courseId)->first();
                                    }

                                    if (!$structure) {
                                        $structure = \App\Models\FeeStructure::first();
                                    }

                                    return new \Illuminate\Support\HtmlString(sprintf(
                                        '<div class="p-4 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                                            <div><strong>Fee Structure Plan Name:</strong> %s</div>
                                            <div><strong>Admission Fee:</strong> PKR %s</div>
                                            <div><strong>Tuition Fee Total:</strong> PKR %s (%d installments of PKR %s)</div>
                                            <div><strong>Verification Fee:</strong> PKR %s</div>
                                            <div><strong>Enrollment Fee:</strong> PKR %s</div>
                                            <div><strong>Examination Fee:</strong> PKR %s</div>
                                            <div><strong>Total Net Package Dues:</strong> <strong class="text-emerald-700">PKR %s</strong></div>
                                        </div>',
                                        e($structure->name ?: ($structure->course?->name . ' Standard Plan')),
                                        number_format($structure->admission_fee, 2),
                                        number_format($structure->total_fee, 2),
                                        $structure->installment_count,
                                        number_format($structure->total_fee / ($structure->installment_count ?: 12), 2),
                                        number_format($structure->verification_fee, 2),
                                        number_format($structure->enrollment_fee, 2),
                                        number_format($structure->examination_fee, 2),
                                        number_format(
                                            (float)$structure->total_fee 
                                            + (float)$structure->admission_fee 
                                            + (float)$structure->verification_fee 
                                            + (float)$structure->enrollment_fee 
                                            + (float)$structure->examination_fee 
                                            + (float)$structure->other_misc, 
                                            2
                                        )
                                    ));
                                })
                                ->columnSpanFull(),

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
                                        ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none'),
                                    Forms\Components\TextInput::make('concession_approver')
                                        ->label('Approving Authority / Officer')
                                        ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none'),
                                    Forms\Components\Textarea::make('concession_reason')
                                        ->label('Reason / Supporting Notes')
                                        ->visible(fn (Forms\Get $get) => $get('concession_type') !== 'none')
                                        ->columnSpanFull(),
                                ])->columns(2),
                        ]),

                    Forms\Components\Wizard\Step::make('Review and Confirm')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Forms\Components\Placeholder::make('review_summary')
                                ->label('Summary Details')
                                ->content(function (Forms\Get $get) {
                                    return new \Illuminate\Support\HtmlString(sprintf(
                                        '<div class="p-4 bg-emerald-50 border border-emerald-100 rounded-lg space-y-1">
                                            <div><strong>Student Name:</strong> %s</div>
                                            <div><strong>CNIC / B-Form #:</strong> %s</div>
                                            <div><strong>Contact Number:</strong> %s</div>
                                            <div><strong>Selected Shift:</strong> %s</div>
                                            <div>Please review all fields across steps 1 to 5. Verify Matric qualifications, document status, and fee plan details. Click the action button to complete submission.</div>
                                        </div>',
                                        $get('applicant_name'),
                                        $get('cnic'),
                                        $get('phone'),
                                        ucfirst($get('shift') ?: 'morning')
                                    ));
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

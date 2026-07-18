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
                    ->visible(fn ($record) => $record && (empty($record->cnic_copy) || empty($record->matric_copy) || empty($record->domicile_copy)))
                    ->columnSpanFull(),
                Forms\Components\Tabs::make('AdmissionForm')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Office & Session')
                            ->icon('heroicon-o-building-library')
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
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('course_id')
                                    ->relationship('course', 'name')
                                    ->label('Offered Course')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        if (!$state) return;
                                        $franchisorId = $get('franchisor_id');
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
                                Forms\Components\TextInput::make('roll_no')
                                    ->label('Roll No'),
                                Forms\Components\TextInput::make('registration_no')
                                    ->label('Registration No'),
                                Forms\Components\TextInput::make('gr_no')
                                    ->label('GR No'),
                                Forms\Components\DatePicker::make('admission_date')
                                    ->label('Admission Date')
                                    ->default(now()),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'waitlisted' => 'Waitlisted',
                                        'document_missing' => 'Document Missing (Warning)',
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Fee & Installments')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\Placeholder::make('fee_structure_guide')
                                    ->label('Standard Course Fee Reference')
                                    ->content(function (Forms\Get $get) {
                                        $courseId = $get('course_id');
                                        $campusId = $get('campus_id') ?: filament()->auth()->user()?->campus_id;
                                        if (!$courseId || !$campusId) {
                                            return 'Please select a course and campus first to view the fee guide.';
                                        }
                                        $structure = \App\Models\FeeStructure::where('course_id', $courseId)
                                            ->where('campus_id', $campusId)
                                            ->first();
                                        if (!$structure) {
                                            return 'No standard fee structure found for this course and campus.';
                                        }
                                        return sprintf(
                                            'Total Package: PKR %s | Standard Installments count: %d | Late fee per day: PKR %s',
                                            number_format($structure->total_fee, 2),
                                            $structure->installment_count,
                                            number_format($structure->late_fee, 2)
                                        );
                                    })
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('custom_installments')
                                    ->label('Custom Installment Schedule')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->placeholder('e.g. 1st Installment')
                                            ->required(),
                                        Forms\Components\TextInput::make('amount')
                                            ->numeric()
                                            ->prefix('PKR')
                                            ->required(),
                                        Forms\Components\DatePicker::make('due_date'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->defaultItems(0),
                            ]),

                        Forms\Components\Tabs\Tab::make('Personal Details')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\FileUpload::make('student_photo')
                                    ->label('Student Passport Photo')
                                    ->directory('student-photos')
                                    ->image()
                                    ->avatar()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('applicant_name')
                                    ->label('Applicant Name (English)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('cnic')
                                    ->label('Student CNIC / B-Form #')
                                    ->required()
                                    ->unique(ignoreRecord: true)
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
                                Forms\Components\TextInput::make('blood_group')
                                    ->label('Blood Group')
                                    ->placeholder('e.g. B+'),
                                Forms\Components\TextInput::make('domicile_district')
                                    ->label('Domicile District')
                                    ->placeholder('e.g. Okara'),
                                Forms\Components\TextInput::make('caste')
                                    ->label('Caste'),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Student Contact #')
                                    ->tel()
                                    ->telRegex('/^[+]?[0-9\s\-()]{7,20}$/')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\Select::make('residence_type')
                                    ->label('Boarder / Non-boarder')
                                    ->options([
                                        'boarder' => 'Boarder (Hostel)',
                                        'non_boarder' => 'Non-boarder (Day Scholar)',
                                    ])
                                    ->default('non_boarder'),
                                Forms\Components\Select::make('shift')
                                    ->label('Student Shift')
                                    ->options([
                                        'morning' => 'Morning',
                                        'evening' => 'Evening',
                                    ])
                                    ->default('morning'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Family & Guardian')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\TextInput::make('father_name')
                                    ->label("Father's / Guardian's Name (English)")
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('father_cnic')
                                    ->label("Father CNIC #")
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('mother_cnic')
                                    ->label("Mother CNIC #")
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('mother_phone')
                                    ->label("Mother's Contact #")
                                    ->tel()
                                    ->telRegex('/^[+]?[0-9\s\-()]{7,20}$/'),
                                Forms\Components\TextInput::make('reference')
                                    ->label('Reference (Who referred)'),
                                Forms\Components\Textarea::make('address')
                                    ->label('Postal Address')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Academic Qualifications')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\Section::make('Matriculation / SSC Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('matric_degree')
                                            ->label('Degree Title')
                                            ->default('Matric Science'),
                                        Forms\Components\TextInput::make('matric_year')
                                            ->label('Passing Year'),
                                        Forms\Components\TextInput::make('matric_roll_no')
                                            ->label('Roll Number'),
                                        Forms\Components\TextInput::make('matric_board')
                                            ->label('Board / University'),
                                        Forms\Components\TextInput::make('matric_obtained_marks')
                                            ->numeric()
                                            ->label('Obtained Marks'),
                                        Forms\Components\TextInput::make('matric_total_marks')
                                            ->numeric()
                                            ->label('Total Marks'),
                                        Forms\Components\TextInput::make('matric_grade')
                                            ->label('Division / Grade'),
                                        Forms\Components\TextInput::make('matric_biology_marks')
                                            ->numeric()
                                            ->label('Biology Marks'),
                                    ])->columns(2),

                                Forms\Components\Section::make('Intermediate / HSSC Details (Optional)')
                                    ->schema([
                                        Forms\Components\TextInput::make('inter_degree')
                                            ->label('Degree Title')
                                            ->placeholder('e.g. F.Sc Pre-Medical'),
                                        Forms\Components\TextInput::make('inter_year')
                                            ->label('Passing Year'),
                                        Forms\Components\TextInput::make('inter_roll_no')
                                            ->label('Roll Number'),
                                        Forms\Components\TextInput::make('inter_board')
                                            ->label('Board / University'),
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

                        Forms\Components\Tabs\Tab::make('Documents Vault')
                            ->icon('heroicon-o-document-duplicate')
                            ->schema([
                                Forms\Components\FileUpload::make('documents_zip_path')
                                    ->label('ZIP Archive of all Documents')
                                    ->directory('student-zips')
                                    ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('missing_documents')
                                    ->label('Missing Documents Details')
                                    ->placeholder('e.g., Intermediate Result Card is missing, Domicile certificate copy is missing...')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),
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
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'waitlisted',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('campus', 'name')
                    ->hidden(fn () => !filament()->auth()->user()->hasRole('Super Admin')),
                Tables\Filters\SelectFilter::make('academicSession')
                    ->relationship('academicSession', 'name'),
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadForm')
                    ->label('Print Form')
                    ->icon('heroicon-o-printer')
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Models\Staff;
use App\Support\DashboardImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Teachers & Staff';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $user = filament()->auth()->user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');

        return $form
            ->schema([
                Forms\Components\Tabs::make('Teacher Profile')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Personal Identity & Campus')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make('Campus & Identity')
                                    ->schema([
                                        Forms\Components\Select::make('campus_id')
                                            ->label('Assigned Campus')
                                            ->options(\App\Models\Campus::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->disabled(!$isSuperAdmin)
                                            ->dehydrated()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $set('employee_id', \App\Services\HR\GenerateEmployeeIdService::generate($state, $get('staff_category') ?? 'TEA'));
                                            }),
                                        Forms\Components\TextInput::make('employee_id')
                                            ->label('Employee ID')
                                            ->disabled()
                                            ->dehydrated()
                                            ->required(),
                                        Forms\Components\Select::make('staff_category')
                                            ->options([
                                                'teaching' => 'Teaching Staff',
                                                'administrative' => 'Administrative Staff',
                                                'support' => 'Support Staff',
                                            ])
                                            ->default('teaching'),
                                        Forms\Components\FileUpload::make('photo_path')
                                            ->label('Profile Photograph')
                                            ->image()
                                            ->disk('public')
                                            ->directory('staff/photos')
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('full_name')
                                            ->label('Full Name')
                                            ->required(),
                                        Forms\Components\TextInput::make('father_or_spouse_name')
                                            ->label('Father / Spouse Name'),
                                        Forms\Components\TextInput::make('cnic')
                                            ->label('CNIC Number')
                                            ->placeholder('38403-1234567-1'),
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

                                Forms\Components\Section::make('Contact & Emergency')
                                    ->schema([
                                        Forms\Components\TextInput::make('phone')->label('Primary Mobile')->tel(),
                                        Forms\Components\TextInput::make('whatsapp')->label('WhatsApp Number'),
                                        Forms\Components\TextInput::make('email')->label('Email Address')->email(),
                                        Forms\Components\Textarea::make('current_address')->columnSpanFull(),
                                        Forms\Components\Textarea::make('permanent_address')->columnSpanFull(),
                                        Forms\Components\TextInput::make('emergency_contact_name')->label('Emergency Contact Name'),
                                        Forms\Components\TextInput::make('emergency_contact_relationship')->label('Relationship'),
                                        Forms\Components\TextInput::make('emergency_contact_phone')->label('Emergency Phone'),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Employment & Posting')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Forms\Components\Section::make('Posting Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('department')->placeholder('e.g. Pharmacy, Allied Health'),
                                        Forms\Components\Select::make('programme_id')
                                            ->label('Associated Course / Programme')
                                            ->options(\App\Models\Course::pluck('name', 'id')),
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
                                            ])->searchable(),
                                        Forms\Components\Select::make('reporting_officer_id')
                                            ->label('Reporting Officer')
                                            ->options(\App\Models\User::pluck('name', 'id'))
                                            ->searchable(),
                                        Forms\Components\Select::make('employment_type')
                                            ->options([
                                                'full_time' => 'Full-time',
                                                'part_time' => 'Part-time',
                                                'visiting' => 'Visiting',
                                                'contract' => 'Contract',
                                            ]),
                                        Forms\Components\Select::make('appointment_status')
                                            ->options([
                                                'probation' => 'Probation',
                                                'permanent' => 'Permanent',
                                                'contract' => 'Fixed-Term Contract',
                                            ]),
                                        Forms\Components\DatePicker::make('joining_date'),
                                        Forms\Components\Select::make('shift')
                                            ->options([
                                                'Morning' => 'Morning Shift',
                                                'Evening' => 'Evening Shift',
                                            ]),
                                        Forms\Components\TextInput::make('biometric_id')->label('Biometric ID'),
                                        Forms\Components\Toggle::make('is_active')->label('Active Status')->default(true),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Academic & Qualifications')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\Section::make('Qualifications')
                                    ->schema([
                                        Forms\Components\Select::make('highest_qualification')
                                            ->options([
                                                'PhD' => 'Doctorate (PhD)',
                                                'MPhil' => 'M.Phil / MS',
                                                'Master' => 'Master Degree',
                                                'Bachelor' => 'Bachelor Degree',
                                                'Diploma' => 'Diploma / Certification',
                                            ]),
                                        Forms\Components\TextInput::make('degree_title'),
                                        Forms\Components\TextInput::make('specialization'),
                                        Forms\Components\TextInput::make('institution'),
                                        Forms\Components\TextInput::make('passing_year')->numeric(),
                                        Forms\Components\TextInput::make('teaching_experience_years')->numeric()->default(0),
                                        Forms\Components\TextInput::make('clinical_experience_years')->numeric()->default(0),
                                        Forms\Components\TextInput::make('previous_employer'),
                                        Forms\Components\TextInput::make('previous_designation'),
                                        Forms\Components\Textarea::make('professional_summary')->columnSpanFull(),
                                    ])->columns(3),

                                Forms\Components\Section::make('Professional Registration')
                                    ->schema([
                                        Forms\Components\TextInput::make('registration_body')->label('Registration Body'),
                                        Forms\Components\TextInput::make('registration_number')->label('Registration Number'),
                                        Forms\Components\DatePicker::make('reg_issue_date'),
                                        Forms\Components\DatePicker::make('reg_expiry_date'),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Salary & Documents')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\Section::make('Remuneration & Deductions')
                                    ->visible($isSuperAdmin)
                                    ->schema([
                                        Forms\Components\TextInput::make('basic_salary')->numeric()->prefix('PKR'),
                                        Forms\Components\TextInput::make('gross_salary')->numeric()->prefix('PKR'),
                                        Forms\Components\TextInput::make('bank_name'),
                                        Forms\Components\TextInput::make('account_number'),
                                        Forms\Components\TextInput::make('iban')->label('IBAN'),
                                    ])->columns(3),

                                Forms\Components\Section::make('Documents')
                                    ->schema([
                                        Forms\Components\FileUpload::make('document_cnic')->directory('staff/documents/cnic'),
                                        Forms\Components\FileUpload::make('document_degree')->directory('staff/documents/degrees'),
                                        Forms\Components\FileUpload::make('document_cv')->directory('staff/documents/cv'),
                                        Forms\Components\FileUpload::make('document_experience')->directory('staff/documents/experience'),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->getStateUsing(fn (Staff $record): ?string => DashboardImage::url($record->photo_path))
                    ->circular()
                    ->size(44)
                    ->defaultImageUrl(fn (Staff $record): string => DashboardImage::avatar($record->full_name)),
                Tables\Columns\TextColumn::make('employee_id')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Teacher Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('designation')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('campus.name')
                    ->label('Campus')
                    ->hidden(fn () => filament()->getCurrentPanel()?->getId() === 'campus'),
                Tables\Columns\TextColumn::make('department')
                    ->searchable(),
                Tables\Columns\TextColumn::make('staff_category')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label('Readiness')
                    ->suffix('%')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('joining_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('campus', 'name')
                    ->hidden(fn () => filament()->getCurrentPanel()?->getId() === 'campus'),
                Tables\Filters\SelectFilter::make('staff_category')
                    ->options([
                        'teaching' => 'Teaching Staff',
                        'administrative' => 'Administrative Staff',
                        'support' => 'Support Staff',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_profile')
                    ->label('Profile Hub')
                    ->icon('heroicon-o-user')
                    ->color('primary')
                    ->url(fn (Staff $record) => Pages\ViewStaffProfile::getUrl(['record' => $record->id])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print_summary')
                    ->label('Summary PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (Staff $record) => route('pdf.teacher-profile-summary', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => filament()->auth()->user()?->hasRole('Super Admin')),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaffWizard::route('/create'),
            'view' => Pages\ViewStaffProfile::route('/{record}'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}

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
                Forms\Components\Tabs::make('AdmissionForm')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Office & Session')
                            ->icon('heroicon-o-building-library')
                            ->schema([
                                Forms\Components\Select::make('campus_id')
                                    ->relationship('campus', 'name')
                                    ->required()
                                    ->hidden(fn () => !auth()->user()->hasRole('Super Admin'))
                                    ->default(fn () => auth()->user()->campus_id),
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
                                    ->preload(),
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
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Personal Details')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\TextInput::make('applicant_name')
                                    ->label('Applicant Name (English)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('applicant_name_urdu')
                                    ->label('نام (طالب علم) اُردُو میں'),
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
                                Forms\Components\TextInput::make('father_name_urdu')
                                    ->label('نام (والد/سرپرست) اُردُو میں'),
                                Forms\Components\TextInput::make('father_cnic')
                                    ->label("Father CNIC #")
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('mother_cnic')
                                    ->label("Mother CNIC #")
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('mother_phone')
                                    ->label("Mother's Contact #")
                                    ->tel(),
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
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('applicant_name')
                    ->label('Applicant Name')
                    ->searchable()
                    ->sortable(),
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
                    ->hidden(fn () => !auth()->user()->hasRole('Super Admin')),
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
        
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('campus_id', auth()->user()->campus_id);
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

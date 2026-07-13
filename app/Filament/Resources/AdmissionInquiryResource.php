<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdmissionInquiryResource\Pages;
use App\Models\VisitorQuery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdmissionInquiryResource extends Resource
{
    protected static ?string $model = VisitorQuery::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationGroup = 'Student Relations';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Admission Inquiries';
    protected static ?string $modelLabel = 'Admission Inquiry';
    protected static ?string $pluralModelLabel = 'Admission Inquiries';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Inquiry & Applicant Details')
                    ->description('Review details of online admission inquiries')
                    ->schema([
                        Forms\Components\Select::make('campus_id')
                            ->relationship('campus', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('visitor_name')
                            ->label('Applicant Name')
                            ->required()
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('father_name')
                            ->label('Father Name')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cnic')
                            ->label('CNIC / B-Form #')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('dob')
                            ->label('Date of Birth')
                            ->disabled(),
                        Forms\Components\TextInput::make('gender')
                            ->label('Gender')
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone / Contact #')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\Select::make('desired_course_id')
                            ->relationship('desiredCourse', 'name')
                            ->label('Desired Course / Program')
                            ->disabled(),
                        Forms\Components\TextInput::make('previous_education')
                            ->label('Matric Details')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('address')
                            ->label('Residential Address')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New Inquiry',
                                'contacted' => 'Contacted / Called Back',
                                'followed_up' => 'Followed Up',
                                'admitted' => 'Converted to Admission',
                                'closed' => 'Closed',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('follow_up_date')
                            ->label('Next Follow-up Date'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Query / Discussion Notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('campus.name')
                    ->label('Campus')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('visitor_name')
                    ->label('Applicant Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cnic')
                    ->label('CNIC')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desiredCourse.name')
                    ->label('Desired Course'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'contacted' => 'warning',
                        'followed_up' => 'primary',
                        'admitted' => 'success',
                        'closed' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('desired_course_id')
                    ->relationship('desiredCourse', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('convertToAdmission')
                    ->label('Convert to Admission')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Convert Inquiry to Admission')
                    ->modalDescription('This will change the query status to Admitted and create a draft Admission record pre-filled with this student\'s details.')
                    ->action(function (VisitorQuery $record) {
                        $record->update(['status' => 'admitted']);

                        $admission = \App\Models\Admission::create([
                            'campus_id' => $record->campus_id,
                            'applicant_name' => $record->visitor_name,
                            'father_name' => $record->father_name ?? ($record->relation_to_student === 'father' ? $record->visitor_name : 'To Be Filled'),
                            'cnic' => $record->cnic ?? ('TEMP-' . time() . '-' . rand(100, 999)),
                            'dob' => $record->dob ? $record->dob->format('Y-m-d') : now()->subYears(18)->format('Y-m-d'),
                            'gender' => $record->gender ?? 'female',
                            'phone' => $record->phone,
                            'address' => $record->address ?? 'Pending address',
                            'course_id' => $record->desired_course_id ?? \App\Models\Course::first()?->id ?? 1,
                            'previous_education' => $record->previous_education,
                            'reference' => 'Inquiry (Online)',
                            'status' => 'pending',
                        ]);

                        return redirect(AdmissionResource::getUrl('edit', ['record' => $admission]));
                    })
                    ->visible(fn (VisitorQuery $record) => $record->status !== 'admitted'),
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
        return parent::getEloquentQuery()->where('came_by', 'website');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmissionInquiries::route('/'),
            'create' => Pages\CreateAdmissionInquiry::route('/create'),
            'edit' => Pages\EditAdmissionInquiry::route('/{record}/edit'),
        ];
    }
}

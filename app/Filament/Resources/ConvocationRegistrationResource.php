<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConvocationRegistrationResource\Pages;
use App\Models\ConvocationRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConvocationRegistrationResource extends Resource
{
    protected static ?string $model = ConvocationRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Student Relations';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Convocation Entries';

    protected static ?string $modelLabel = 'Convocation Entry';

    protected static ?string $pluralModelLabel = 'Convocation Entries';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Student & Academic Information')
                ->schema([
                    Forms\Components\Select::make('campus_id')
                        ->relationship('campus', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true)->whereRaw('LOWER(TRIM(name)) <> ?', ['main campus']))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required()
                        ->disabled(fn () => ! filament()->auth()->user()?->hasRole('Super Admin'))
                        ->dehydrated(),
                    Forms\Components\Select::make('academic_session_id')
                        ->relationship(
                            'academicSession',
                            'name',
                            modifyQueryUsing: fn ($query, Forms\Get $get) => $query->when($get('campus_id'), fn ($builder, $campusId) => $builder->where('campus_id', $campusId)),
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('course_id')
                        ->relationship('course', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true))
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('student_name')
                        ->required()
                        ->maxLength(255),
                ])->columns(2),
            Forms\Components\Section::make('Convocation Attendee')
                ->schema([
                    Forms\Components\TextInput::make('attendee_name')
                        ->label('Attendee Name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('attendee_relation')
                        ->label('Relation with Student')
                        ->options(self::relationOptions())
                        ->required(),
                    Forms\Components\TextInput::make('attendee_cnic')
                        ->label('Attendee CNIC')
                        ->placeholder('00000-0000000-0')
                        ->mask('99999-9999999-9')
                        ->required()
                        ->maxLength(15),
                    Forms\Components\Select::make('status')
                        ->options([
                            'registered' => 'Registered',
                            'checked_in' => 'Checked In',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required()
                        ->default('registered'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Submitted')->dateTime('d M Y, h:i A')->sortable(),
                Tables\Columns\TextColumn::make('student_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('campus.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('academicSession.name')->label('Session')->searchable(),
                Tables\Columns\TextColumn::make('course.name')->label('Program')->searchable(),
                Tables\Columns\TextColumn::make('attendee_name')->label('Attendee')->searchable(),
                Tables\Columns\TextColumn::make('attendee_relation')->label('Relation')->formatStateUsing(fn (string $state) => self::relationOptions()[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('attendee_cnic')->label('Attendee CNIC')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'registered' => 'info',
                        'checked_in' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus_id')->relationship('campus', 'name')->visible(fn () => filament()->auth()->user()?->hasRole('Super Admin')),
                Tables\Filters\SelectFilter::make('academic_session_id')->relationship('academicSession', 'name')->label('Session'),
                Tables\Filters\SelectFilter::make('course_id')->relationship('course', 'name')->label('Program'),
                Tables\Filters\SelectFilter::make('status')->options([
                    'registered' => 'Registered',
                    'checked_in' => 'Checked In',
                    'cancelled' => 'Cancelled',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConvocationRegistrations::route('/'),
            'create' => Pages\CreateConvocationRegistration::route('/create'),
            'edit' => Pages\EditConvocationRegistration::route('/{record}/edit'),
        ];
    }

    public static function relationOptions(): array
    {
        return [
            'self' => 'Self',
            'father' => 'Father',
            'mother' => 'Mother',
            'brother' => 'Brother',
            'sister' => 'Sister',
            'spouse' => 'Spouse',
            'guardian' => 'Guardian',
            'other' => 'Other',
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorQueryResource\Pages;
use App\Models\VisitorQuery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitorQueryResource extends Resource
{
    protected static ?string $model = VisitorQuery::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Student Relations';
    protected static ?int $navigationSort = 0; // Top item in Student Relations

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Visitor & Inquiry Details')
                    ->description('Log new walk-in or phone inquiry details')
                    ->schema([
                        Forms\Components\Select::make('campus_id')
                            ->relationship('campus', 'name')
                            ->required()
                            ->hidden(fn () => !auth()->user()->hasRole('Super Admin'))
                            ->default(auth()->user()->campus_id),
                        Forms\Components\TextInput::make('visitor_name')
                            ->label('Visitor / Student Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone / Contact #')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('relation_to_student')
                            ->label('Relation to Student')
                            ->options([
                                'self' => 'Self (Student Himself/Herself)',
                                'father' => 'Father',
                                'mother' => 'Mother',
                                'guardian' => 'Guardian',
                                'relative' => 'Relative',
                                'other' => 'Other',
                            ])
                            ->default('self')
                            ->required(),
                        Forms\Components\Select::make('came_by')
                            ->label('Came By / Referral Source')
                            ->options([
                                'walk_in' => 'Walk-In / Direct Visit',
                                'social_media' => 'Social Media (FB/Insta/WhatsApp)',
                                'banner_poster' => 'Flex Banner / Poster',
                                'friend_alumni' => 'Friend / Alumni Reference',
                                'newspaper' => 'Newspaper / Pamphlet',
                                'other' => 'Other',
                            ])
                            ->default('walk_in')
                            ->required(),
                        Forms\Components\Select::make('desired_course_id')
                            ->relationship('desiredCourse', 'name')
                            ->label('Desired Course / Program')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New Inquiry',
                                'contacted' => 'Contacted / Called Back',
                                'followed_up' => 'Followed Up',
                                'admitted' => 'Converted to Admission',
                                'closed' => 'Closed',
                            ])
                            ->default('new')
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
                Tables\Columns\TextColumn::make('visitor_name')
                    ->label('Visitor Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('relation_to_student')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('desiredCourse.name')
                    ->label('Desired Course')
                    ->placeholder('General Inquiry'),
                Tables\Columns\TextColumn::make('came_by')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state))),
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
                            'father_name' => $record->relation_to_student === 'father' ? $record->visitor_name : 'To Be Filled',
                            'cnic' => 'TEMP-' . time() . '-' . rand(100, 999),
                            'dob' => now()->subYears(18)->format('Y-m-d'),
                            'gender' => 'male',
                            'phone' => $record->phone,
                            'address' => 'Pending address',
                            'course_id' => $record->desired_course_id ?? \App\Models\Course::first()?->id ?? 1,
                            'reference' => 'Inquiry (' . $record->came_by . ')',
                            'status' => 'pending',
                        ]);

                        return redirect(AdmissionResource::getUrl('edit', ['record' => $admission]));
                    }),
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
        $user = auth()->user();

        if ($user && $user->campus_id && !$user->hasRole('Super Admin')) {
            $query->where('campus_id', $user->campus_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitorQueries::route('/'),
            'create' => Pages\CreateVisitorQuery::route('/create'),
            'edit' => Pages\EditVisitorQuery::route('/{record}/edit'),
        ];
    }
}

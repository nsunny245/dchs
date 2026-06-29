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

    public static function shouldRegisterNavigation(): bool
    {
        return !auth()->user()->hasRole('Super Admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Applicant Information')
                    ->description('Personal details of the new applicant')
                    ->schema([
                        Forms\Components\Select::make('campus_id')
                            ->relationship('campus', 'name')
                            ->required()
                            ->hidden(fn () => !auth()->user()->hasRole('Super Admin'))
                            ->default(auth()->user()->campus_id),
                        Forms\Components\TextInput::make('applicant_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('father_name')
                            ->required()
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
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Academic Details')
                    ->schema([
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'name')
                            ->required(),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('campus.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('applicant_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
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
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadLetter')
                    ->label('PDF')
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
        
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('campus_id', auth()->user()->campus_id);
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

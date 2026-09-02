<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimetableResource\Pages;
use App\Models\Timetable;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimetableResource extends Resource
{
    protected static ?string $model = Timetable::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?string $navigationLabel = 'Program Timetables';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return ! filament()->auth()->user()->hasRole('Admission Officer');
    }

    public static function canViewAny(): bool
    {
        return ! filament()->auth()->user()->hasRole('Admission Officer');
    }

    public static function form(Form $form): Form
    {
        // Custom TimetableWizard handles multi-step form execution
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Timetable Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('campus.name')
                    ->label('Campus')
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Program / Course')
                    ->sortable(),
                Tables\Columns\TextColumn::make('semester_name')
                    ->label('Semester / Year')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('section_name')
                    ->label('Section')
                    ->badge()
                    ->color('slate'),
                Tables\Columns\TextColumn::make('effective_from')
                    ->label('Effective Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'info' => 'pending_approval',
                        'success' => 'published',
                        'gray' => 'archived',
                    ])
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('slots_count')
                    ->counts('slots')
                    ->label('Classes')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('campus', 'name')
                    ->hidden(fn () => ! filament()->auth()->user()->hasRole('Super Admin')),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'name')
                    ->label('Program'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('edit_wizard')
                        ->label('Edit Timetable')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary')
                        ->url(fn (Timetable $record) => static::getUrl('edit', ['record' => $record->id])),
                    Tables\Actions\Action::make('export_pdf')
                        ->label('PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('gray')
                        ->url(fn (Timetable $record) => route('pdf.timetable', $record->id))
                        ->openUrlInNewTab(),
                    Tables\Actions\DeleteAction::make(),
                ])->label('Actions')->button()->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['campus', 'course', 'academicSession', 'slots']);

        $user = filament()->auth()->user();
        if ($user && ! $user->hasRole('Super Admin') && $user->campus_id !== null) {
            $query->where('campus_id', $user->campus_id);
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
            'index' => Pages\ListTimetables::route('/'),
            'create' => Pages\TimetableWizard::route('/create'),
            'edit' => Pages\TimetableWizard::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarkResource\Pages;
use App\Models\Mark;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarkResource extends Resource
{
    protected static ?string $model = Mark::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return !auth()->user()->hasRole('Admission Officer');
    }

    public static function canViewAny(): bool
    {
        return !auth()->user()->hasRole('Admission Officer');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Mark Entry')
                    ->schema([
                        Forms\Components\Select::make('campus_id')
                            ->relationship('campus', 'name')
                            ->required()
                            ->hidden(fn () => !auth()->user()->hasRole('Super Admin'))
                            ->default(auth()->user()->campus_id),
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('exam_id')
                            ->relationship('exam', 'exam_name')
                            ->required(),
                        Forms\Components\TextInput::make('obtained_marks')
                            ->numeric()
                            ->required(),
                        Forms\Components\Textarea::make('remarks')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.full_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exam.exam_name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('obtained_marks')
                    ->sortable(),
                Tables\Columns\TextColumn::make('exam.max_marks')
                    ->label('Max Marks'),
                Tables\Columns\TextColumn::make('remarks')
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('campus', 'name')
                    ->hidden(fn () => !auth()->user()->hasRole('Super Admin')),
                Tables\Filters\SelectFilter::make('exam')
                    ->relationship('exam', 'exam_name'),
            ])
            ->actions([
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
            'index' => Pages\ListMarks::route('/'),
            'create' => Pages\CreateMark::route('/create'),
            'edit' => Pages\EditMark::route('/{record}/edit'),
        ];
    }
}

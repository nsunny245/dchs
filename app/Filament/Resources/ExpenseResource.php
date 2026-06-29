<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Financial Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('expense_category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('campus_id')
                    ->relationship('campus', 'name')
                    ->placeholder('Global / Head Office')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->user()->campus_id),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->required(),
                Forms\Components\DatePicker::make('expense_date')
                    ->default(now())
                    ->required(),
                Forms\Components\FileUpload::make('receipt')
                    ->directory('expense-receipts')
                    ->image()
                    ->maxSize(2048),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('campus.name')
                    ->placeholder('Global / Head Office')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PKR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('expense_category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('campus_id')
                    ->relationship('campus', 'name')
                    ->label('Campus'),
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
        $user = auth()->user();

        if ($user && $user->campus_id && !$user->hasRole('Super Admin')) {
            $query->where('campus_id', $user->campus_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}

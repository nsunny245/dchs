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
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 2;

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
                    ->default(fn () => filament()->auth()->user()->campus_id),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->live()
                    ->required(),
                Forms\Components\Select::make('expense_source')
                    ->label('Expense Source')
                    ->options([
                        'college_revenue' => 'College Revenue / Income',
                        'chairman_naveed' => 'Paid by Chairman (Dr. Naveed)',
                        'split' => 'Split Payment (Revenue & Chairman)',
                    ])
                    ->default('college_revenue')
                    ->live()
                    ->required(),
                Forms\Components\TextInput::make('college_revenue_amount')
                    ->label('College Revenue Portion')
                    ->numeric()
                    ->prefix('PKR')
                    ->visible(fn (Forms\Get $get) => $get('expense_source') === 'split')
                    ->required(fn (Forms\Get $get) => $get('expense_source') === 'split')
                    ->rules([
                        fn (Forms\Get $get) => function (string $attribute, $value, $fail) use ($get) {
                            $source = $get('expense_source');
                            $amount = (float) $get('amount');
                            $chairmanAmount = (float) $get('chairman_naveed_amount');
                            if ($source === 'split' && (round((float)$value + $chairmanAmount, 2) !== round($amount, 2))) {
                                $fail("The sum of College Revenue (" . $value . ") and Chairman (" . $chairmanAmount . ") portions must equal the total expense amount (" . $amount . ").");
                            }
                        }
                    ]),
                Forms\Components\TextInput::make('chairman_naveed_amount')
                    ->label('Chairman Portion')
                    ->numeric()
                    ->prefix('PKR')
                    ->visible(fn (Forms\Get $get) => $get('expense_source') === 'split')
                    ->required(fn (Forms\Get $get) => $get('expense_source') === 'split'),
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
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
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
                Tables\Columns\TextColumn::make('expense_source')
                    ->label('Source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'college_revenue' => 'success',
                        'chairman_naveed' => 'info',
                        'split' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'college_revenue' => 'College Revenue',
                        'chairman_naveed' => 'Chairman Naveed',
                        'split' => 'Split Payment',
                        default => $state,
                    }),
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
        $user = filament()->auth()->user();

        if ($user && $user->campus_id !== null) {
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

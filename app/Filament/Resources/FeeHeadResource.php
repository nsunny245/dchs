<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeeHeadResource\Pages;
use App\Models\FeeHead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeeHeadResource extends Resource
{
    protected static ?string $model = FeeHead::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Fee Heads';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Fee Head Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('e.g., ADMISSION, TUITION'),
                        Forms\Components\Select::make('category')
                            ->options([
                                'admission' => 'Admission',
                                'tuition' => 'Tuition',
                                'registration' => 'Registration',
                                'examination' => 'Examination',
                                'laboratory' => 'Laboratory',
                                'library' => 'Library',
                                'security' => 'Security',
                                'transport' => 'Transport',
                                'hostel' => 'Hostel',
                                'affiliation' => 'Affiliation',
                                'uniform' => 'Uniform',
                                'clinical' => 'Clinical',
                                'fine' => 'Fine',
                                'discount' => 'Discount',
                                'scholarship' => 'Scholarship',
                                'arrears' => 'Arrears',
                                'miscellaneous' => 'Miscellaneous',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('default_amount')
                            ->numeric()
                            ->prefix('PKR'),
                        Forms\Components\Select::make('applies_to')
                            ->options([
                                'new_enrollment' => 'New Enrollment',
                                'monthly_installment' => 'Monthly / Installment',
                                'both' => 'Both',
                            ])
                            ->required()
                            ->default('both'),
                        Forms\Components\Toggle::make('is_discount')
                            ->label('Is Discount / Scholarship Adjustment')
                            ->default(false),
                        Forms\Components\Toggle::make('is_refundable')
                            ->label('Is Refundable Dues')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'name')
                            ->nullable()
                            ->label('Course-specific')
                            ->helperText('Leave blank for all courses.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('default_amount')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('applies_to')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->placeholder('Global'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category'),
                Tables\Filters\SelectFilter::make('applies_to'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeHeads::route('/'),
            'create' => Pages\CreateFeeHead::route('/create'),
            'edit' => Pages\EditFeeHead::route('/{record}/edit'),
        ];
    }
}

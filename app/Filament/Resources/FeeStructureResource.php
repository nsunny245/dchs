<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeeStructureResource\Pages;
use App\Models\FeeStructure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeeStructureResource extends Resource
{
    protected static ?string $model = FeeStructure::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = filament()->auth()->user();
        if (!$user) return false;

        return $user->email === 'admin@admin.com' 
            || $user->hasRole('Super Admin') 
            || $user->campus_id === null 
            || filament()->getCurrentPanel()?->getId() === 'admin';
    }

    public static function canViewAny(): bool
    {
        $user = filament()->auth()->user();
        if (!$user) return false;

        return $user->email === 'admin@admin.com' 
            || $user->hasRole('Super Admin') 
            || $user->campus_id === null 
            || filament()->getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('A. Scope Settings')
                    ->description('Define the plan name, target course, session, and campus availability')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Fee Structure Name')
                            ->placeholder('e.g. LHV Standard Package')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'name')
                            ->label('Assigned Course / Program')
                            ->required(),
                        Forms\Components\Select::make('academic_session_id')
                            ->relationship('academicSession', 'name')
                            ->label('Target Academic Session')
                            ->placeholder('Default / All Sessions')
                            ->nullable(),
                        Forms\Components\Select::make('campus_id')
                            ->label('Assigned Campus')
                            ->options(function () {
                                return [
                                    '' => '🌐 All Campuses (Global Access for All Campuses)',
                                ] + \App\Models\Campus::pluck('name', 'id')->toArray();
                            })
                            ->placeholder('🌐 All Campuses (Global Access for All Campuses)')
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('B. Fee Components')
                    ->description('Configure the precise breakdown of all fees. The total is calculated automatically.')
                    ->schema([
                        Forms\Components\TextInput::make('admission_fee')
                            ->label('Admission Fee')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('total_fee')
                            ->label('Total Tuition Fee')
                            ->numeric()
                            ->prefix('PKR')
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('verification_fee')
                            ->label('Verification Fee')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('enrollment_fee')
                            ->label('Enrollment Fee')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('examination_fee')
                            ->label('Examination Fee')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('hostel_dues')
                            ->label('Hostel Dues (If applicable)')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('other_misc')
                            ->label('Other Misc / Extra Charges')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->live()
                            ->required(),

                        Forms\Components\Placeholder::make('grand_total_preview')
                            ->label('Total Package Cost')
                            ->content(function (Forms\Get $get) {
                                $total = (float)$get('admission_fee') 
                                    + (float)$get('total_fee') 
                                    + (float)$get('verification_fee') 
                                    + (float)$get('enrollment_fee') 
                                    + (float)$get('examination_fee') 
                                    + (float)$get('hostel_dues') 
                                    + (float)$get('other_misc');
                                return new \Illuminate\Support\HtmlString('<strong class="text-xl text-emerald-700 font-bold">PKR ' . number_format($total, 2) . '</strong>');
                            })
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('C. Installment Template & Preview')
                    ->description('Define installment count and review the generated schedules')
                    ->schema([
                        Forms\Components\TextInput::make('installment_count')
                            ->label('Number of tuition installments')
                            ->numeric()
                            ->default(12)
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('late_fee')
                            ->label('Late Fee (Per day penalty)')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(100)
                            ->required(),

                        Forms\Components\Placeholder::make('installment_schedule_preview')
                            ->label('Installments Schedule Preview')
                            ->content(function (Forms\Get $get) {
                                $count = (int)$get('installment_count') ?: 12;
                                $tuitionTotal = (float)$get('total_fee');
                                $monthly = $count > 0 ? $tuitionTotal / $count : 0;

                                $rows = [];
                                for ($i = 1; $i <= $count; $i++) {
                                    $rows[] = sprintf(
                                        '<tr>
                                            <td class="px-4 py-2 border border-slate-200">%d</td>
                                            <td class="px-4 py-2 border border-slate-200">Tuition Installment #%d</td>
                                            <td class="px-4 py-2 border border-slate-200 text-right">PKR %s</td>
                                        </tr>',
                                        $i,
                                        $i,
                                        number_format($monthly, 2)
                                    );
                                }

                                return new \Illuminate\Support\HtmlString(sprintf(
                                    '<table class="w-full text-sm border-collapse border border-slate-200">
                                        <thead>
                                            <tr class="bg-slate-100">
                                                <th class="px-4 py-2 border border-slate-200 text-left">No.</th>
                                                <th class="px-4 py-2 border border-slate-200 text-left">Voucher Title</th>
                                                <th class="px-4 py-2 border border-slate-200 text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            %s
                                        </tbody>
                                    </table>',
                                    implode('', $rows)
                                ));
                            })
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Fee Structure Plan Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Target Course')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('campus.name')
                    ->label('Campus')
                    ->default('🌐 All Campuses')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_fee')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('installment_count')
                    ->sortable(),
                Tables\Columns\TextColumn::make('late_fee')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('admission_fee')
                    ->money('PKR')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('hostel_dues')
                    ->money('PKR')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('verification_fee')
                    ->money('PKR')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('enrollment_fee')
                    ->money('PKR')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('examination_fee')
                    ->money('PKR')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('other_misc')
                    ->money('PKR')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('campus', 'name')
                    ->hidden(fn () => filament()->getCurrentPanel()?->getId() === 'campus'),
            ])
            ->actions([
                Tables\Actions\Action::make('duplicateForSession')
                    ->label('Duplicate for Session')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('academic_session_id')
                            ->relationship('academicSession', 'name')
                            ->label('Target New Academic Session')
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('New Fee Plan Name')
                            ->default(fn ($record) => $record->name . ' (New Session)')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $newRecord = $record->replicate();
                        $newRecord->academic_session_id = $data['academic_session_id'];
                        $newRecord->name = $data['name'];
                        $newRecord->save();

                        \Filament\Notifications\Notification::make()
                            ->title('Fee Structure Duplicated')
                            ->body("Created new fee plan for target session.")
                            ->success()
                            ->send();
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
        
        if (!filament()->auth()->user()->hasRole('Super Admin')) {
            $query->where('campus_id', filament()->auth()->user()->campus_id);
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
            'index' => Pages\ListFeeStructures::route('/'),
            'create' => Pages\CreateFeeStructure::route('/create'),
            'edit' => Pages\EditFeeStructure::route('/{record}/edit'),
        ];
    }
}

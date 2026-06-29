<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FranchisorResource\Pages;
use App\Models\Franchisor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FranchisorResource extends Resource
{
    protected static ?string $model = Franchisor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Institution / Franchisor Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'inbound' => 'Inbound (External inst. enrolling via DCHS)',
                        'outbound' => 'Outbound (DCHS enrolling via External inst.)',
                    ])
                    ->required()
                    ->default('inbound'),
                Forms\Components\TextInput::make('contact_person')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inbound' => 'info',
                        'outbound' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'inbound' => 'Inbound',
                        'outbound' => 'Outbound',
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFranchisors::route('/'),
            'create' => Pages\CreateFranchisor::route('/create'),
            'edit' => Pages\EditFranchisor::route('/{record}/edit'),
        ];
    }
}

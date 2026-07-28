<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Settings Configuration')
                    ->schema([
                        Forms\Components\Select::make('campus_id')
                            ->relationship('campus', 'name')
                            ->placeholder('Global / Website Setting')
                            ->nullable()
                            ->hidden(fn () => !filament()->auth()->user()->hasRole('Super Admin'))
                            ->default(filament()->auth()->user()->campus_id),
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->live()
                            ->maxLength(255),
                        Forms\Components\Select::make('value')
                            ->label('Franchisor User Account')
                            ->options(fn () => \App\Models\User::pluck('name', 'id'))
                            ->visible(fn (Forms\Get $get) => in_array($get('key'), ['franchisor_inbound_user_id', 'franchisor_outbound_user_id']))
                            ->required(fn (Forms\Get $get) => in_array($get('key'), ['franchisor_inbound_user_id', 'franchisor_outbound_user_id'])),
                        Forms\Components\Textarea::make('value')
                            ->label('Value')
                            ->hidden(fn (Forms\Get $get) => in_array($get('key'), ['franchisor_inbound_user_id', 'franchisor_outbound_user_id']))
                            ->required(fn (Forms\Get $get) => !in_array($get('key'), ['franchisor_inbound_user_id', 'franchisor_outbound_user_id']))
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(),
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(function (Setting $record, string $state) {
                        if (in_array($record->key, ['franchisor_inbound_user_id', 'franchisor_outbound_user_id'])) {
                            return \App\Models\User::find($state)?->name ?? $state;
                        }
                        return $state;
                    })
                    ->limit(50),
                Tables\Columns\TextColumn::make('campus.name')
                    ->sortable()
                    ->default('Global / Website')
                    ->hidden(fn () => !filament()->auth()->user()->hasRole('Super Admin')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('campus', 'name')
                    ->hidden(fn () => !filament()->auth()->user()->hasRole('Super Admin')),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}

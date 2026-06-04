<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('phone')->tel()->nullable(),
            TextInput::make('password')
                ->password()
                ->required(fn (string $context): bool => $context === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->revealable(),
            
            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable()
                ->required()
                ->label('Assign Roles'),

            Select::make('campus_id')
                ->relationship('campus', 'name')
                ->searchable()
                ->preload()
                ->nullable()
                ->label('Assigned Campus')
                ->helperText('Leave blank for Group Admins. Required for Campus staff.'),

            Toggle::make('status')->default(true)->label('Active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('campus.name')->label('Campus')->placeholder('Group HQ'),
            TextColumn::make('roles.name')
                ->badge()
                ->colors([
                    'primary' => 'Super Admin',
                    'warning' => 'Campus Principal',
                    'success' => 'Faculty',
                    'info' => 'Admission Officer',
                    'danger' => 'Finance',
                ])
                ->label('Roles'),
            TextColumn::make('status')
                ->badge()
                ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                ->colors([
                    'success' => true,
                    'danger' => false,
                ])
                ->label('Status'),
        ])
        ->filters([])
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            \Filament\Tables\Actions\BulkActionGroup::make([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // 🔒 Authorization
    public static function canViewAny(): bool { return auth()->user()->can('view any user'); }
    public static function canCreate(): bool { return auth()->user()->can('create user'); }
    public static function canUpdate(\Illuminate\Database\Eloquent\Model $record): bool { return auth()->user()->can('update user'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return auth()->user()->can('delete user'); }
}

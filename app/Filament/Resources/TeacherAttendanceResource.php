<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAttendanceResource\Pages;
use App\Models\Staff;
use App\Models\TeacherAttendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherAttendanceResource extends Resource
{
    protected static ?string $model = TeacherAttendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Teacher Attendance';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([Forms\Components\Select::make('staff_id')->label('Teacher / Staff')->options(fn () => Staff::query()->where('is_active', true)->orderBy('full_name')->pluck('full_name', 'id'))->searchable()->required(), Forms\Components\DatePicker::make('date')->default(now())->required(), Forms\Components\Select::make('status')->options(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'leave' => 'Leave'])->default('present')->required(), Forms\Components\Textarea::make('remarks'), Forms\Components\Hidden::make('campus_id')->default(fn () => filament()->auth()->user()?->campus_id), Forms\Components\Hidden::make('marked_by')->default(fn () => auth()->id())])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('id')->label('S.No')->rowIndex(), Tables\Columns\TextColumn::make('date')->date('d M Y')->sortable(), Tables\Columns\TextColumn::make('staff.full_name')->label('Teacher')->searchable()->sortable(), Tables\Columns\TextColumn::make('staff.employee_id')->label('Employee ID'), Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
            'present' => 'success','absent' => 'danger','late' => 'warning','leave' => 'info',default => 'gray'
        }), Tables\Columns\TextColumn::make('remarks')->limit(40)])->filters([Tables\Filters\SelectFilter::make('status')->options(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'leave' => 'Leave'])])->actions([
            Tables\Actions\ActionGroup::make([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])->label('Actions')->button()->color('primary'),
        ])->headerActions([Tables\Actions\CreateAction::make()->label('Mark Attendance')]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListTeacherAttendances::route('/'), 'create' => Pages\CreateTeacherAttendance::route('/create'), 'edit' => Pages\EditTeacherAttendance::route('/{record}/edit')];
    }
}

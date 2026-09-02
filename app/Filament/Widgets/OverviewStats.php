<?php

namespace App\Filament\Widgets;

use App\Models\Campus;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = filament()->auth()->user();
        $isSuperAdmin = $user && ($user->campus_id === null || filament()->getCurrentPanel()?->getId() === 'admin');

        // Hierarchy-aware queries
        $studentCount = $isSuperAdmin ? Student::count() : Student::where('campus_id', $user->campus_id)->count();
        $campusCount = $isSuperAdmin ? Campus::where('is_active', true)->count() : 1;
        $courseCount = Course::where('is_active', true)->count();
        $facultyCount = $isSuperAdmin ? User::whereHas('roles', fn ($q) => $q->where('name', 'Faculty'))->count() : User::where('campus_id', $user->campus_id)->whereHas('roles', fn ($q) => $q->where('name', 'Faculty'))->count();

        return [
            Stat::make('Total Students', $studentCount)
                ->description('Active enrollments')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Active Campuses', $campusCount)
                ->description('Operational locations')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
            Stat::make('Programs Offered', $courseCount)
                ->description('Across all campuses')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
            Stat::make('Faculty Members', $facultyCount)
                ->description('Teaching staff')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}

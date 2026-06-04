<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Student;
use App\Models\Course;
use App\Models\Campus;
use App\Models\Admission;

class OverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        
        // Hierarchy-aware queries
        $studentCount = $isSuperAdmin ? Student::count() : Student::where('campus_id', $user->campus_id)->count();
        $campusCount = $isSuperAdmin ? Campus::where('is_active', true)->count() : 1;
        $courseCount = Course::where('is_active', true)->count();
        $pendingApps = $isSuperAdmin ? Admission::where('status', 'pending')->count() : Admission::where('campus_id', $user->campus_id)->where('status', 'pending')->count();
        $approvedApps = $isSuperAdmin ? Admission::where('status', 'approved')->count() : Admission::where('campus_id', $user->campus_id)->where('status', 'approved')->count();
        $facultyCount = $isSuperAdmin ? \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'Faculty'))->count() : \App\Models\User::where('campus_id', $user->campus_id)->whereHas('roles', fn($q) => $q->where('name', 'Faculty'))->count();

        return [
            Stat::make('Total Students', $studentCount)
                ->description('Active enrollments')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Active Campuses', $campusCount)
                ->description('Operational locations')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),
            Stat::make('Programs Offered', $courseCount)
                ->description('Across all campuses')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),
            Stat::make('Pending Applications', $pendingApps)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
            Stat::make('Approved Admissions', $approvedApps)
                ->description('Ready for onboarding')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),
            Stat::make('Faculty Members', $facultyCount)
                ->description('Teaching staff')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}

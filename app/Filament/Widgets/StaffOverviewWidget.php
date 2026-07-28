<?php

namespace App\Filament\Widgets;

use App\Models\Staff;
use App\Models\LeaveRequest;
use App\Models\AgreementVersion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StaffOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = filament()->auth()->user();
        $staffQuery = Staff::query();
        $leaveQuery = LeaveRequest::query();
        $agreementQuery = AgreementVersion::query();

        if ($user && $user->campus_id !== null) {
            $staffQuery->where('campus_id', $user->campus_id);
            $leaveQuery->where('campus_id', $user->campus_id);
            $agreementQuery->whereHas('staff', fn($q) => $q->where('campus_id', $user->campus_id));
        }

        $totalTeachers = (clone $staffQuery)->count();
        $onProbation = (clone $staffQuery)->whereHas('employmentRecords', fn($q) => $q->where('is_current', true)->where('appointment_status', 'probation'))->count();
        $permanent = (clone $staffQuery)->whereHas('employmentRecords', fn($q) => $q->where('is_current', true)->where('appointment_status', 'permanent'))->count();
        $contract = (clone $staffQuery)->whereHas('employmentRecords', fn($q) => $q->where('is_current', true)->where('appointment_status', 'contract'))->count();
        $onLeaveToday = (clone $leaveQuery)->where('status', 'approved')->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now())->count();
        $pendingLeave = (clone $leaveQuery)->where('status', 'pending')->count();
        $missingDocs = (clone $staffQuery)->whereDoesntHave('documents', fn($q) => $q->where('document_type', 'cnic'))->count();
        $awaitingAgreements = (clone $agreementQuery)->where('status', 'generated')->count();

        return [
            Stat::make('Total Teachers', $totalTeachers)
                ->description('Active & Registered Staff')
                ->color('primary'),
            Stat::make('On Probation', $onProbation)
                ->description('Pending Confirmation')
                ->color('warning'),
            Stat::make('Permanent Staff', $permanent)
                ->description('Confirmed Appointments')
                ->color('success'),
            Stat::make('Contract Staff', $contract)
                ->description('Fixed-Term Employment')
                ->color('info'),
            Stat::make('On Leave Today', $onLeaveToday)
                ->description('Approved Leave Records')
                ->color('danger'),
            Stat::make('Pending Leave Requests', $pendingLeave)
                ->description('Awaiting HR Decision')
                ->color('warning'),
            Stat::make('Missing CNIC Docs', $missingDocs)
                ->description('Incomplete Profiles')
                ->color('danger'),
            Stat::make('Agreements Pending Sign', $awaitingAgreements)
                ->description('Generated & Awaiting Scan')
                ->color('warning'),
        ];
    }
}

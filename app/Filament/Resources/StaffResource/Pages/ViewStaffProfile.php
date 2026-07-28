<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Models\Staff;
use App\Models\EmploymentRecord;
use App\Models\SalaryRecord;
use App\Models\LeaveRequest;
use App\Models\StaffDocument;
use App\Models\AgreementVersion;
use App\Services\HR\CalculateProfileCompletionService;
use App\Services\HR\EvaluateAgreementReadinessService;
use App\Services\HR\GenerateTeacherAgreementAction;
use Filament\Resources\Pages\Page;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ViewStaffProfile extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = StaffResource::class;
    protected static string $view = 'filament.resources.staff-resource.pages.view-staff-profile';
    protected static ?string $title = 'Teacher Profile Hub';

    public Staff $record;
    public string $activeTab = 'overview';

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function mount(int $record): void
    {
        $this->record = Staff::with([
            'campus', 'user', 'academics', 'registrations',
            'employmentRecords.campus', 'salaryRecords',
            'documents', 'leaveRequests', 'agreementVersions'
        ])->findOrFail($record);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // --- QUICK ACTIONS ---

    public function confirmEmployment(): void
    {
        $user = filament()->auth()->user();
        if (!$user || !$user->hasRole('Super Admin')) {
            Notification::make()->title('Unauthorized')->danger()->send();
            return;
        }

        $current = $this->record->currentEmployment ?? $this->record->employmentRecords()->first();
        if ($current) {
            $current->update([
                'appointment_status' => 'permanent',
                'confirmation_date' => now()->format('Y-m-d'),
            ]);
        }

        Notification::make()->title('Permanent Employment Confirmed')->success()->send();
        $this->mount($this->record->id);
    }

    public function extendProbation(string $newEndDate): void
    {
        $user = filament()->auth()->user();
        if (!$user || !$user->hasRole('Super Admin')) {
            Notification::make()->title('Unauthorized')->danger()->send();
            return;
        }

        $current = $this->record->currentEmployment ?? $this->record->employmentRecords()->first();
        if ($current) {
            $current->update([
                'probation_end_date' => $newEndDate,
            ]);
        }

        Notification::make()->title('Probation Extended')->warning()->send();
        $this->mount($this->record->id);
    }

    public function generateAgreement(): void
    {
        try {
            $agreement = GenerateTeacherAgreementAction::execute($this->record, auth()->id());
            Notification::make()
                ->title('Employment Agreement V' . $agreement->version . ' Generated')
                ->success()
                ->send();
            $this->mount($this->record->id);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Agreement Generation Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

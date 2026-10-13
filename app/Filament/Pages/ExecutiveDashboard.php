<?php

namespace App\Filament\Pages;

use App\Filament\Resources\AdmissionResource;
use App\Filament\Resources\AttendanceResource;
use App\Filament\Resources\ExpenseResource;
use App\Filament\Resources\FeeCollectionResource;
use App\Filament\Resources\FeeVoucherResource;
use App\Filament\Resources\FranchisorResource;
use App\Filament\Resources\StaffResource;
use App\Filament\Resources\StudentResource;
use App\Filament\Resources\SuperAdminFranchisorPaymentResource;
use App\Filament\Resources\TeacherAttendanceResource;
use App\Filament\Resources\TimetableResource;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Franchisor;
use App\Models\FranchisorStudentPayment;
use App\Services\Dashboard\ExecutiveDashboardMetrics;
use Filament\Pages\Dashboard;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ExecutiveDashboard extends Dashboard
{
    protected static string $view = 'filament.pages.executive-dashboard';

    protected static ?string $slug = 'dashboard';

    protected static ?string $title = 'Operations Dashboard';

    #[Url(as: 'campus')]
    public string $campusFilter = 'all';

    #[Url]
    public string $month = '';

    public function mount(): void
    {
        $user = filament()->auth()->user();
        $this->month = $this->validMonth($this->month) ? $this->month : now()->format('Y-m');
        if (! $user->hasRole('Super Admin')) {
            $this->campusFilter = (string) $user->campus_id;
        } elseif ($this->campusFilter !== 'all' && ! $this->campuses()->whereKey($this->campusFilter)->exists()) {
            $this->campusFilter = 'all';
        }
    }

    public function updatedMonth(string $value): void
    {
        if (! $this->validMonth($value)) {
            $this->month = now()->format('Y-m');
        }
    }

    public function updatedCampusFilter(string $value): void
    {
        $user = filament()->auth()->user();
        if (! $user->hasRole('Super Admin')) {
            $this->campusFilter = (string) $user->campus_id;

            return;
        }
        if ($value !== 'all' && ! $this->campuses()->whereKey($value)->exists()) {
            $this->campusFilter = 'all';
        }
    }

    protected function getViewData(): array
    {
        $user = filament()->auth()->user();
        $panel = filament()->getCurrentPanel()?->getId();
        $campusId = $this->campusFilter === 'all' ? null : (int) $this->campusFilter;
        $data = app(ExecutiveDashboardMetrics::class)->for($campusId, $this->month);

        $campuses = $user->hasRole('Super Admin')
            ? $this->campuses()->with('principals')->get()->sortBy(fn (Campus $campus) => [
                strtolower((string) $campus->city) === 'okara' ? 0 : 1,
                strtolower((string) ($campus->city ?: $campus->name)),
            ])->values()
            : collect();
        $franchiseDues = $user->hasRole('Super Admin') ? (float) FranchisorStudentPayment::sum('total_amount') : 0;
        $franchisePaid = $user->hasRole('Super Admin') ? (float) FranchisorStudentPayment::sum('paid_amount') : 0;

        return $data + [
            'isSuperAdmin' => $user->hasRole('Super Admin'),
            'campuses' => $campuses,
            'selectedCampus' => $campusId ? Campus::find($campusId)?->name : 'All campuses',
            'links' => [
                'students' => StudentResource::getUrl('index', panel: $panel),
                'staff' => StaffResource::getUrl('index', panel: $panel),
                'admissions' => AdmissionResource::getUrl('index', panel: $panel),
                'attendance' => AttendanceResource::getUrl('index', panel: $panel),
                'teacher-attendance' => TeacherAttendanceResource::getUrl('index', panel: $panel),
                'fee-collections' => FeeCollectionResource::getUrl('index', panel: $panel),
                'fee-vouchers' => FeeVoucherResource::getUrl('index', panel: $panel),
                'expenses' => ExpenseResource::getUrl('index', panel: $panel),
                'timetables' => TimetableResource::getUrl('index', panel: $panel),
                'franchisors' => $user->hasRole('Super Admin') ? FranchisorResource::getUrl('index', panel: $panel) : null,
                'franchisor-payments' => $user->hasRole('Super Admin') ? SuperAdminFranchisorPaymentResource::getUrl('index', panel: $panel) : null,
            ],
            'franchise' => [
                'active' => $user->hasRole('Super Admin') ? Franchisor::where('is_active', true)->count() : 0,
                'seats' => $user->hasRole('Super Admin') ? Admission::whereNotNull('franchisor_id')->count() : 0,
                'dues' => $franchiseDues,
                'paid' => $franchisePaid,
                'outstanding' => max(0, $franchiseDues - $franchisePaid),
                'recent' => $user->hasRole('Super Admin')
                    ? FranchisorStudentPayment::with(['franchisor', 'admission'])->latest()->limit(5)->get()
                    : collect(),
            ],
        ];
    }

    private function validMonth(string $month): bool
    {
        return preg_match('/^20\d{2}-(0[1-9]|1[0-2])$/', $month) === 1;
    }

    private function campuses(): Builder
    {
        return Campus::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM(name)) <> ?', ['main campus']);
    }
}

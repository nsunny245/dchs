<?php

namespace App\Services\Dashboard;

use App\Models\Admission;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\FeePayment;
use App\Models\FeeVoucher;
use App\Models\Staff;
use App\Models\Student;
use App\Models\TeacherAttendance;
use App\Models\Timetable;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class ExecutiveDashboardMetrics
{
    public function for(?int $campusId, string $month): array
    {
        $start = CarbonImmutable::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->endOfMonth();
        $today = now()->toDateString();

        $students = $this->campus(Student::query(), $campusId);
        $staff = $this->campus(Staff::query(), $campusId);
        $admissions = $this->campus(Admission::query(), $campusId);
        $vouchers = $this->campus(FeeVoucher::query(), $campusId);
        $expenses = $this->campus(Expense::query(), $campusId);
        $payments = FeePayment::query()->when(
            $campusId,
            fn (Builder $query) => $query->whereHas('student', fn (Builder $student) => $student->where('campus_id', $campusId)),
        );

        $activeStudents = (clone $students)->where(function (Builder $query) {
            $query->where('is_active', true)->orWhere('status', 'active');
        })->count();
        $activeStaff = (clone $staff)->where(function (Builder $query) {
            $query->where('is_active', true)->orWhere('status', 'active');
        })->count();
        $monthPayments = (clone $payments)->whereBetween('payment_date', [$start, $end]);
        $monthExpenses = (clone $expenses)->whereBetween('expense_date', [$start, $end]);
        $collected = (float) $monthPayments->sum('amount');
        $spent = (float) $monthExpenses->sum('college_revenue_amount');
        $outstanding = (float) (clone $vouchers)
            ->whereNotIn('status', ['paid', 'cancelled', 'void', 'waived'])
            ->sum('balance_amount');

        $studentAttendance = $this->attendanceBreakdown(
            $this->campus(Attendance::query(), $campusId)->whereDate('date', $today),
            $activeStudents,
        );
        $staffAttendance = $this->attendanceBreakdown(
            $this->campus(TeacherAttendance::query(), $campusId)->whereDate('date', $today),
            $activeStaff,
        );

        $paymentByDay = (clone $payments)->whereBetween('payment_date', [$start, $end])
            ->selectRaw('payment_date as day, SUM(amount) as total')
            ->groupBy('payment_date')->pluck('total', 'day');
        $expenseByDay = (clone $expenses)->whereBetween('expense_date', [$start, $end])
            ->selectRaw('expense_date as day, SUM(college_revenue_amount) as total')
            ->groupBy('expense_date')->pluck('total', 'day');
        $admissionsByDay = (clone $admissions)->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')->pluck('total', 'day');

        $days = collect(range(1, $end->day))->map(fn (int $day) => $start->setDay($day));
        $cashSeries = $days->map(fn (CarbonImmutable $date) => [
            'day' => $date->day,
            'label' => $date->format('d M'),
            'in' => (float) ($paymentByDay[$date->toDateString()] ?? 0),
            'out' => (float) ($expenseByDay[$date->toDateString()] ?? 0),
        ])->all();
        $admissionSeries = $days->map(fn (CarbonImmutable $date) => [
            'day' => $date->day,
            'label' => $date->format('d M'),
            'value' => (int) ($admissionsByDay[$date->toDateString()] ?? 0),
        ])->all();

        return [
            'periodLabel' => $start->format('F Y'),
            'generatedAt' => now()->format('d M Y, h:i A'),
            'metrics' => [
                ['label' => 'Active students', 'value' => number_format($activeStudents), 'detail' => number_format((clone $students)->count()).' total records', 'tone' => 'gold', 'icon' => 'heroicon-o-user-group', 'route' => 'students'],
                ['label' => 'Active staff', 'value' => number_format($activeStaff), 'detail' => number_format((clone $staff)->count()).' total staff', 'tone' => 'blue', 'icon' => 'heroicon-o-identification', 'route' => 'staff'],
                ['label' => 'New admissions', 'value' => number_format((clone $admissions)->whereBetween('created_at', [$start, $end])->count()), 'detail' => $start->format('F').' intake', 'tone' => 'orange', 'icon' => 'heroicon-o-academic-cap', 'route' => 'admissions'],
                ['label' => 'Collected', 'value' => 'PKR '.number_format($collected), 'detail' => $start->format('F').' fee receipts', 'tone' => 'green', 'icon' => 'heroicon-o-banknotes', 'route' => 'fee-collections'],
                ['label' => 'Outstanding', 'value' => 'PKR '.number_format($outstanding), 'detail' => 'Open student balances', 'tone' => 'magenta', 'icon' => 'heroicon-o-receipt-percent', 'route' => 'fee-vouchers'],
                ['label' => 'Expenses', 'value' => 'PKR '.number_format($spent), 'detail' => $start->format('F').' college spending', 'tone' => 'red', 'icon' => 'heroicon-o-arrow-trending-down', 'route' => 'expenses'],
                ['label' => 'Net cash flow', 'value' => 'PKR '.number_format($collected - $spent), 'detail' => $collected >= $spent ? 'Positive for selected month' : 'Deficit for selected month', 'tone' => $collected >= $spent ? 'teal' : 'red', 'icon' => 'heroicon-o-chart-bar-square', 'route' => 'fee-collections'],
                ['label' => 'Published timetables', 'value' => number_format($this->campus(Timetable::query(), $campusId)->where('status', 'published')->count()), 'detail' => 'Currently active schedules', 'tone' => 'navy', 'icon' => 'heroicon-o-calendar-days', 'route' => 'timetables'],
            ],
            'finance' => [
                'collected' => $collected,
                'spent' => $spent,
                'outstanding' => $outstanding,
                'collectionRate' => ($collected + $outstanding) > 0 ? round(($collected / ($collected + $outstanding)) * 100) : 0,
            ],
            'studentAttendance' => $studentAttendance,
            'staffAttendance' => $staffAttendance,
            'cashSeries' => $cashSeries,
            'admissionSeries' => $admissionSeries,
            'recentAdmissions' => (clone $admissions)->with(['course', 'campus'])->latest()->limit(6)->get(),
            'exceptions' => [
                'overdueVouchers' => (clone $vouchers)->whereDate('due_date', '<', $today)->whereNotIn('status', ['paid', 'cancelled', 'void', 'waived'])->count(),
                'missingDocuments' => (clone $admissions)->where('is_document_deficient', true)->count(),
                'pendingAdmissions' => (clone $admissions)->whereIn('status', ['pending', 'draft'])->count(),
            ],
        ];
    }

    private function campus(Builder $query, ?int $campusId): Builder
    {
        return $query->when($campusId, fn (Builder $builder) => $builder->where('campus_id', $campusId));
    }

    private function attendanceBreakdown(Builder $query, int $population): array
    {
        $counts = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $marked = (int) $counts->sum();
        $rows = collect(['present', 'absent', 'late', 'leave'])->map(function (string $status) use ($counts, $population) {
            $count = (int) ($counts[$status] ?? 0);

            return ['label' => ucfirst($status), 'count' => $count, 'percent' => $population > 0 ? round(($count / $population) * 100) : 0, 'status' => $status];
        });
        $rows->push(['label' => 'Not marked', 'count' => max(0, $population - $marked), 'percent' => $population > 0 ? round((max(0, $population - $marked) / $population) * 100) : 0, 'status' => 'unmarked']);

        return ['population' => $population, 'marked' => $marked, 'rows' => $rows->all()];
    }
}

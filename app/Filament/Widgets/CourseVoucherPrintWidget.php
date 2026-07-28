<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Course;
use App\Models\Campus;

class CourseVoucherPrintWidget extends Widget
{
    protected static string $view = 'filament.widgets.course-voucher-print-widget';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return !request()->routeIs('*.pages.dashboard');
    }

    public $showModal = false;
    public $selectedCourseId = null;
    public $selectedCourseName = '';
    public $selectedCampusId = null;
    public $selectedMonth = '';

    public $courses = [];
    public $campuses = [];
    public $months = [];
    public $isSuperAdmin = false;

    public function mount()
    {
        $user = filament()->auth()->user();
        $this->isSuperAdmin = $user && $user->hasRole('Super Admin') && filament()->getCurrentPanel()?->getId() === 'admin';

        $this->courses = Course::all();
        $this->campuses = Campus::all();

        // Generate the last 2 and next 6 months for the dropdown select
        $this->months = [];
        for ($i = -2; $i <= 6; $i++) {
            $date = now()->addMonths($i);
            $this->months[$date->format('Y-m')] = $date->format('F Y');
        }

        // Default selected month is the current month
        $this->selectedMonth = now()->format('Y-m');

        if (!$this->isSuperAdmin && $user) {
            $this->selectedCampusId = $user->campus_id;
        }
    }

    public function openPrintModal($courseId)
    {
        $course = Course::find($courseId);
        if ($course) {
            $this->selectedCourseId = $course->id;
            $this->selectedCourseName = $course->name;
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedCourseId = null;
        $this->selectedCourseName = '';
    }
}

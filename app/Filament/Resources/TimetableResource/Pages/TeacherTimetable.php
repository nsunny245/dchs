<?php

namespace App\Filament\Resources\TimetableResource\Pages;

use App\Filament\Resources\TimetableResource;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Timetable;
use App\Services\Timetable\TeacherTimetableLayout;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class TeacherTimetable extends Page
{
    protected static string $resource = TimetableResource::class;

    protected static string $view = 'filament.resources.timetable-resource.pages.teacher-timetable';

    protected static ?string $title = 'Teacher Timetable';

    #[Locked]
    public ?int $recordId = null;

    #[Locked]
    public int $version = 0;

    public string $timetableTitle = 'Weekly Class Timetable';

    public ?int $campusId = null;

    public ?int $courseId = null;

    public string $effectiveFrom = '';

    public ?string $notes = null;

    public array $sheet = [];

    public bool $preview = false;

    public bool $dirty = false;

    #[Locked]
    public string $status = 'draft';

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function mount(?int $record = null): void
    {
        $this->authorizeEditor();
        $this->effectiveFrom = now()->toDateString();
        $this->campusId = filament()->auth()->user()->campus_id;
        $this->sheet = TeacherTimetableLayout::template();
        if ($record) {
            $timetable = $this->accessibleRecord($record);
            abort_unless($timetable->teacher_layout, 404);
            $this->recordId = $timetable->id;
            $this->version = $timetable->layout_version;
            $this->sheet = $timetable->teacher_layout;
            $this->timetableTitle = $timetable->title;
            $this->campusId = $timetable->campus_id;
            $this->courseId = $timetable->course_id;
            $this->effectiveFrom = $timetable->effective_from->toDateString();
            $this->notes = $timetable->notes;
            $this->status = $timetable->status;
        }
    }

    private function authorizeEditor(): void
    {
        abort_unless(filament()->auth()->check() && TimetableResource::canViewAny(), 403);
    }

    private function accessibleRecord(int $id): Timetable
    {
        $this->authorizeEditor();
        $record = TimetableResource::getEloquentQuery()->findOrFail($id);
        abort_unless(TimetableResource::canEdit($record), 403);

        return $record;
    }

    public function updated(): void
    {
        $this->dirty = true;
    }

    public function addGroup(): void
    {
        if (count($this->sheet['groups']) < 14) {
            $this->sheet['groups'][] = TeacherTimetableLayout::group();
            $this->dirty = true;
        }
    }

    public function removeGroup(int $group): void
    {
        if (isset($this->sheet['groups'][$group]) && count($this->sheet['groups']) > 1) {
            array_splice($this->sheet['groups'], $group, 1);
            $this->dirty = true;
        }
    }

    public function addTeacher(int $group): void
    {
        if (isset($this->sheet['groups'][$group]) && count($this->sheet['groups'][$group]['teachers']) < 60) {
            $this->sheet['groups'][$group]['teachers'][] = ['name' => '', 'cells' => array_fill(0, count($this->sheet['groups'][$group]['periods']), TeacherTimetableLayout::cell())];
            $this->dirty = true;
        }
    }

    public function removeTeacher(int $group, int $row): void
    {
        if (isset($this->sheet['groups'][$group]['teachers'][$row]) && count($this->sheet['groups'][$group]['teachers']) > 1) {
            array_splice($this->sheet['groups'][$group]['teachers'], $row, 1);
            $this->dirty = true;
        }
    }

    public function moveTeacher(int $group, int $row, int $direction): void
    {
        if (! in_array($direction, [-1, 1], true)) {
            return;
        }
        $rows = $this->sheet['groups'][$group]['teachers'] ?? [];
        $target = $row + $direction;
        if (isset($rows[$row], $rows[$target])) {
            [$rows[$row], $rows[$target]] = [$rows[$target], $rows[$row]];
            $this->sheet['groups'][$group]['teachers'] = $rows;
            $this->dirty = true;
        }
    }

    public function addPeriod(int $group): void
    {
        if (! isset($this->sheet['groups'][$group]) || count($this->sheet['groups'][$group]['periods']) >= 16) {
            return;
        }
        $periods = $this->sheet['groups'][$group]['periods'];
        $start = end($periods)['end'];
        $end = date('H:i', strtotime($start.' +40 minutes'));
        $this->sheet['groups'][$group]['periods'][] = ['label' => 'Period '.(count($periods) + 1), 'start' => $start, 'end' => $end, 'is_break' => false];
        foreach ($this->sheet['groups'][$group]['teachers'] as &$teacher) {
            $teacher['cells'][] = TeacherTimetableLayout::cell();
        }
        $this->dirty = true;
    }

    public function removePeriod(int $group, int $period): void
    {
        if (isset($this->sheet['groups'][$group]['periods'][$period]) && count($this->sheet['groups'][$group]['periods']) > 1) {
            array_splice($this->sheet['groups'][$group]['periods'], $period, 1);
            foreach ($this->sheet['groups'][$group]['teachers'] as &$teacher) {
                array_splice($teacher['cells'], $period, 1);
            }
            $this->dirty = true;
        }
    }

    public function save(bool $publish = false): void
    {
        $this->authorizeEditor();
        $user = filament()->auth()->user();
        $this->validate([
            'timetableTitle' => 'required|string|max:191',
            'campusId' => ['required', Rule::exists('campuses', 'id')],
            'courseId' => ['required', Rule::exists('courses', 'id')],
            'effectiveFrom' => 'required|date_format:Y-m-d',
            'notes' => 'nullable|string|max:5000',
        ]);
        abort_unless($user->hasRole('Super Admin') || (int) $user->campus_id === $this->campusId, 403);
        $layout = TeacherTimetableLayout::validate($this->sheet);
        if ($publish && TeacherTimetableLayout::classCount($layout) === 0) {
            throw ValidationException::withMessages(['layout' => 'Add at least one class before publishing.']);
        }
        $record = DB::transaction(function () use ($layout, $publish, $user) {
            if ($this->recordId) {
                $record = $this->accessibleRecord($this->recordId);
                $record = Timetable::whereKey($record->id)->lockForUpdate()->firstOrFail();
                if ($record->layout_version !== $this->version) {
                    throw ValidationException::withMessages(['layout' => 'Someone else saved this timetable. Copy your changes, then reload before saving.']);
                }
            } else {
                abort_unless(TimetableResource::canCreate(), 403);
                $record = new Timetable(['created_by' => $user->id]);
            }
            $record->fill([
                'title' => trim($this->timetableTitle), 'campus_id' => $this->campusId,
                'course_id' => $this->courseId, 'effective_from' => $this->effectiveFrom,
                'teacher_layout' => $layout, 'layout_version' => $this->version + 1,
                'working_days' => array_values(array_unique(array_merge(...array_column($layout['groups'], 'days')))),
                'status' => $publish ? 'published' : 'draft', 'notes' => $this->notes,
                'updated_by' => $user->id, 'published_by' => $publish ? $user->id : null,
                'published_at' => $publish ? now() : null,
            ])->save();

            return $record;
        });
        $this->recordId = $record->id;
        $this->version = $record->layout_version;
        $this->status = $record->status;
        $this->dirty = false;
        Notification::make()->success()->title($publish ? 'Teacher timetable published' : 'Teacher timetable saved')->send();
    }

    protected function getViewData(): array
    {
        $user = filament()->auth()->user();

        return [
            'campuses' => Campus::query()->when(! $user->hasRole('Super Admin'), fn ($q) => $q->whereKey($user->campus_id))->get(),
            'courses' => Course::orderBy('name')->get(),
            'days' => TeacherTimetableLayout::DAYS,
        ];
    }
}

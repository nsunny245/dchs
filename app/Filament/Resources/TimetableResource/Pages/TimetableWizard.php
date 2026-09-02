<?php

namespace App\Filament\Resources\TimetableResource\Pages;

use App\Filament\Resources\TimetableResource;
use App\Models\AcademicSession;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Models\TimetableSubject;
use App\Services\Timetable\TimetableBuilderService;
use App\Services\Timetable\TimetableConflictService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class TimetableWizard extends Page
{
    protected static string $resource = TimetableResource::class;

    protected static string $view = 'filament.resources.timetable-resource.pages.timetable-wizard';

    protected static ?string $title = 'Program Timetable Workspace';

    public ?int $recordId = null;

    public int $currentStep = 1;

    // Step 1: Setup Header
    public ?int $campus_id = null;

    public ?int $course_id = null;

    public ?int $academic_session_id = null;

    public string $batch_name = 'Batch 2025-2027';

    public string $semester_name = 'Year 1';

    public string $section_name = 'Section A';

    public string $shift = 'morning';

    public string $timetable_title = '';

    public bool $titleCustomized = false;

    public string $effective_from = '';

    public ?string $effective_to = null;

    public array $working_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    public int $default_period_duration = 45;

    public ?string $notes = null;

    public string $status = 'draft';

    // Step 2: Subjects Selection
    public array $availableSubjects = [];

    public array $selectedSubjectIds = [];

    public array $subjectTeachers = [];

    public array $subjectPeriods = [];

    // Step 3: Slot Drawer / Modal State
    public bool $isSlotModalOpen = false;

    public ?int $editingSlotId = null;

    public string $modalDay = 'monday';

    public string $modalStartTime = '08:30';

    public string $modalEndTime = '09:15';

    public ?int $modalSubjectId = null;

    public ?int $modalTeacherId = null;

    public ?int $modalRoomId = null;

    public string $modalClassType = 'Theory';

    public ?string $modalNotes = null;

    public array $modalConflictErrors = [];

    public array $modalConflictWarnings = [];

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function mount(?int $record = null): void
    {
        $user = filament()->auth()->user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');

        $this->effective_from = now()->format('Y-m-d');

        if ($record) {
            $timetable = Timetable::with(['timetableSubjects', 'slots'])->findOrFail($record);
            $this->recordId = $timetable->id;
            $this->campus_id = $timetable->campus_id;
            $this->course_id = $timetable->course_id;
            $this->academic_session_id = $timetable->academic_session_id;
            $this->batch_name = $timetable->batch_name ?? 'Batch 2025-2027';
            $this->semester_name = $timetable->semester_name ?? 'Year 1';
            $this->section_name = $timetable->section_name ?? 'Section A';
            $this->shift = $timetable->shift ?? 'morning';
            $this->timetable_title = $timetable->title;
            $this->titleCustomized = true;
            $this->effective_from = $timetable->effective_from ? $timetable->effective_from->format('Y-m-d') : now()->format('Y-m-d');
            $this->effective_to = $timetable->effective_to ? $timetable->effective_to->format('Y-m-d') : null;
            $this->working_days = $timetable->working_days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $this->default_period_duration = $timetable->default_period_duration ?? 45;
            $this->notes = $timetable->notes;
            $this->status = $timetable->status;

            $this->selectedSubjectIds = $timetable->timetableSubjects->pluck('subject_id')->filter()->toArray();
            foreach ($timetable->timetableSubjects as $ts) {
                if ($ts->subject_id) {
                    $this->subjectTeachers[$ts->subject_id] = $ts->default_teacher_id;
                    $this->subjectPeriods[$ts->subject_id] = $ts->required_periods_per_week;
                }
            }

            $this->loadSubjectsForProgram();
            $this->currentStep = 3; // Jump directly to grid builder on edit
        } else {
            $this->campus_id = $user ? $user->campus_id : Campus::first()?->id;
            $this->course_id = Course::first()?->id;
            $this->academic_session_id = AcademicSession::first()?->id;
            $this->autoGenerateTitle();
            $this->loadSubjectsForProgram();
        }
    }

    public function updatedCourseId(): void
    {
        $this->autoGenerateTitle();
        $this->loadSubjectsForProgram();
    }

    public function updatedSemesterName(): void
    {
        $this->autoGenerateTitle();
        $this->loadSubjectsForProgram();
    }

    public function updatedSectionName(): void
    {
        $this->autoGenerateTitle();
    }

    public function updatedTimetableTitle(): void
    {
        $this->titleCustomized = true;
    }

    public function autoGenerateTitle(): void
    {
        if ($this->titleCustomized) {
            return;
        }
        $course = Course::find($this->course_id);
        $courseName = $course ? $course->name : 'Program';
        $this->timetable_title = "{$courseName} {$this->semester_name} - {$this->section_name} Timetable";
    }

    public function loadSubjectsForProgram(): void
    {
        if (! $this->course_id) {
            $this->availableSubjects = [];

            return;
        }

        $query = Subject::where('course_id', $this->course_id)->where('is_active', true);
        if ($this->semester_name) {
            $query->where(function ($q) {
                $q->where('semester_year', $this->semester_name)
                    ->orWhereNull('semester_year');
            });
        }

        $subjects = $query->get();

        // Fallback: If no semester-specific subjects, load all course subjects
        if ($subjects->count() === 0) {
            $subjects = Subject::where('course_id', $this->course_id)->where('is_active', true)->get();
        }

        $this->availableSubjects = $subjects->toArray();

        // Auto-select mandatory subjects by default
        if (empty($this->selectedSubjectIds)) {
            $this->selectedSubjectIds = $subjects->pluck('id')->toArray();
            foreach ($subjects as $s) {
                $this->subjectPeriods[$s->id] = $s->weekly_periods ?? 4;
            }
        }
    }

    public function goToStep(int $step): void
    {
        if ($step === 2 && ! $this->validateStep1()) {
            return;
        }
        if ($step === 3 && ! $this->validateStep2()) {
            return;
        }
        $this->currentStep = $step;
    }

    public function validateStep1(): bool
    {
        if (! $this->campus_id || ! $this->course_id || ! $this->timetable_title || ! $this->effective_from) {
            Notification::make()->title('Please fill all required setup fields.')->warning()->send();

            return false;
        }

        // Save or update timetable header
        if (! $this->recordId) {
            $timetable = Timetable::create([
                'uuid' => (string) Str::uuid(),
                'title' => $this->timetable_title,
                'campus_id' => $this->campus_id,
                'course_id' => $this->course_id,
                'academic_session_id' => $this->academic_session_id,
                'batch_name' => $this->batch_name,
                'semester_name' => $this->semester_name,
                'section_name' => $this->section_name,
                'shift' => $this->shift,
                'effective_from' => $this->effective_from,
                'effective_to' => $this->effective_to,
                'working_days' => $this->working_days,
                'default_period_duration' => $this->default_period_duration,
                'status' => 'draft',
                'created_by' => auth()->id(),
                'notes' => $this->notes,
            ]);
            $this->recordId = $timetable->id;
        } else {
            $timetable = Timetable::find($this->recordId);
            $timetable->update([
                'title' => $this->timetable_title,
                'campus_id' => $this->campus_id,
                'course_id' => $this->course_id,
                'academic_session_id' => $this->academic_session_id,
                'batch_name' => $this->batch_name,
                'semester_name' => $this->semester_name,
                'section_name' => $this->section_name,
                'shift' => $this->shift,
                'effective_from' => $this->effective_from,
                'effective_to' => $this->effective_to,
                'working_days' => $this->working_days,
                'default_period_duration' => $this->default_period_duration,
                'notes' => $this->notes,
            ]);
        }

        return true;
    }

    public function nextToSubjects(): void
    {
        if ($this->validateStep1()) {
            $this->loadSubjectsForProgram();
            $this->currentStep = 2;
        }
    }

    public function validateStep2(): bool
    {
        if (empty($this->selectedSubjectIds)) {
            Notification::make()->title('Please select at least one subject to include in the timetable.')->warning()->send();

            return false;
        }

        $timetable = Timetable::find($this->recordId);
        if ($timetable) {
            TimetableBuilderService::syncSubjects(
                $timetable,
                $this->selectedSubjectIds,
                $this->subjectTeachers,
                $this->subjectPeriods
            );
        }

        return true;
    }

    public function nextToBuilder(): void
    {
        if ($this->validateStep2()) {
            $this->currentStep = 3;
        }
    }

    // Slot Modal Handlers
    public function openAddSlotModal(string $day, string $startTime, string $endTime): void
    {
        $this->resetSlotModal();
        $this->modalDay = strtolower($day);
        $this->modalStartTime = $startTime;
        $this->modalEndTime = $endTime;

        // Auto preselect first available subject if present
        if (! empty($this->selectedSubjectIds)) {
            $firstSubId = $this->selectedSubjectIds[0];
            $this->modalSubjectId = $firstSubId;
            $this->modalTeacherId = $this->subjectTeachers[$firstSubId] ?? null;
        }

        $this->isSlotModalOpen = true;
    }

    public function updatedModalSubjectId($value): void
    {
        if ($value) {
            $subject = Subject::find($value);
            if ($subject) {
                $this->modalClassType = $subject->default_class_type ?? 'Theory';
            }
            if (isset($this->subjectTeachers[$value]) && $this->subjectTeachers[$value]) {
                $this->modalTeacherId = $this->subjectTeachers[$value];
            }
        }
        $this->checkModalConflicts();
    }

    public function updatedModalTeacherId(): void
    {
        $this->checkModalConflicts();
    }

    public function updatedModalRoomId(): void
    {
        $this->checkModalConflicts();
    }

    public function checkModalConflicts(): void
    {
        $validation = TimetableConflictService::validateSlot([
            'timetable_id' => $this->recordId,
            'day_of_week' => $this->modalDay,
            'start_time' => $this->modalStartTime,
            'end_time' => $this->modalEndTime,
            'teacher_id' => $this->modalTeacherId,
            'room_id' => $this->modalRoomId,
        ], $this->editingSlotId);

        $this->modalConflictErrors = $validation['errors'];
        $this->modalConflictWarnings = $validation['warnings'];
    }

    public function editSlot(int $slotId): void
    {
        $slot = TimetableSlot::find($slotId);
        if (! $slot) {
            return;
        }

        $this->editingSlotId = $slot->id;
        $this->modalDay = strtolower($slot->day_of_week);
        $this->modalStartTime = date('H:i', strtotime($slot->start_time));
        $this->modalEndTime = date('H:i', strtotime($slot->end_time));
        $this->modalSubjectId = $slot->subject_id;
        $this->modalTeacherId = $slot->teacher_id;
        $this->modalRoomId = $slot->room_id;
        $this->modalClassType = $slot->class_type ?? 'Theory';
        $this->modalNotes = $slot->notes;

        $this->checkModalConflicts();
        $this->isSlotModalOpen = true;
    }

    public function saveSlot(): void
    {
        if (! $this->modalSubjectId) {
            Notification::make()->title('Please select a subject for this slot.')->warning()->send();

            return;
        }

        $this->checkModalConflicts();
        if (! empty($this->modalConflictErrors)) {
            Notification::make()->title('Cannot Save Slot due to Conflicts')->danger()->body(implode('<br>', $this->modalConflictErrors))->send();

            return;
        }

        $subject = Subject::find($this->modalSubjectId);
        $subjectName = $subject ? $subject->name : 'Subject';

        $ttSubject = TimetableSubject::where('timetable_id', $this->recordId)
            ->where('subject_id', $this->modalSubjectId)
            ->first();

        if ($this->editingSlotId) {
            $slot = TimetableSlot::find($this->editingSlotId);
            $slot->update([
                'timetable_subject_id' => $ttSubject?->id,
                'subject_id' => $this->modalSubjectId,
                'subject_name' => $subjectName,
                'teacher_id' => $this->modalTeacherId,
                'room_id' => $this->modalRoomId,
                'class_type' => $this->modalClassType,
                'day_of_week' => strtolower($this->modalDay),
                'start_time' => $this->modalStartTime,
                'end_time' => $this->modalEndTime,
                'notes' => $this->modalNotes,
                'updated_by' => auth()->id(),
            ]);
            Notification::make()->title('Class slot updated successfully.')->success()->send();
        } else {
            TimetableSlot::create([
                'uuid' => (string) Str::uuid(),
                'timetable_id' => $this->recordId,
                'timetable_subject_id' => $ttSubject?->id,
                'subject_id' => $this->modalSubjectId,
                'subject_name' => $subjectName,
                'teacher_id' => $this->modalTeacherId,
                'room_id' => $this->modalRoomId,
                'class_type' => $this->modalClassType,
                'day_of_week' => strtolower($this->modalDay),
                'start_time' => $this->modalStartTime,
                'end_time' => $this->modalEndTime,
                'notes' => $this->modalNotes,
                'created_by' => auth()->id(),
            ]);
            Notification::make()->title('Class slot added to grid.')->success()->send();
        }

        $timetable = Timetable::find($this->recordId);
        if ($timetable) {
            TimetableBuilderService::recalculateScheduledCounts($timetable);
        }

        $this->resetSlotModal();
    }

    public function deleteSlot(int $slotId): void
    {
        $slot = TimetableSlot::find($slotId);
        if ($slot) {
            $slot->delete();
            $timetable = Timetable::find($this->recordId);
            if ($timetable) {
                TimetableBuilderService::recalculateScheduledCounts($timetable);
            }
            Notification::make()->title('Slot removed.')->info()->send();
        }
        $this->resetSlotModal();
    }

    public function resetSlotModal(): void
    {
        $this->isSlotModalOpen = false;
        $this->editingSlotId = null;
        $this->modalSubjectId = null;
        $this->modalTeacherId = null;
        $this->modalRoomId = null;
        $this->modalClassType = 'Theory';
        $this->modalNotes = null;
        $this->modalConflictErrors = [];
        $this->modalConflictWarnings = [];
    }

    public function saveDraft(): void
    {
        $timetable = Timetable::find($this->recordId);
        if ($timetable) {
            $timetable->update(['status' => 'draft']);
            Notification::make()->title('Timetable saved as Draft.')->success()->send();
            $this->currentStep = 5;
        }
    }

    public function publishTimetable(): void
    {
        $timetable = Timetable::find($this->recordId);
        if (! $timetable) {
            return;
        }

        // Run publication checks
        $slotsCount = $timetable->slots()->count();
        if ($slotsCount === 0) {
            Notification::make()->title('Cannot Publish Empty Timetable. Please schedule at least one class slot.')->danger()->send();

            return;
        }

        $timetable->update([
            'status' => 'published',
            'published_by' => auth()->id(),
            'published_at' => now(),
        ]);

        Notification::make()->title('Timetable Published Successfully!')->success()->body('The program timetable is now active and visible to authorized staff and students.')->send();
        $this->status = 'published';
        $this->currentStep = 5;
    }

    public function duplicateTimetable(): void
    {
        $original = Timetable::with(['timetableSubjects', 'slots'])->find($this->recordId);
        if (! $original) {
            return;
        }

        $newSection = $original->section_name === 'Section A' ? 'Section B' : 'Section B Duplicate';
        $newTitle = "{$original->title} ({$newSection})";

        $duplicate = Timetable::create([
            'uuid' => (string) Str::uuid(),
            'title' => $newTitle,
            'campus_id' => $original->campus_id,
            'course_id' => $original->course_id,
            'academic_session_id' => $original->academic_session_id,
            'batch_name' => $original->batch_name,
            'semester_name' => $original->semester_name,
            'section_name' => $newSection,
            'shift' => $original->shift,
            'effective_from' => now()->format('Y-m-d'),
            'working_days' => $original->working_days,
            'default_period_duration' => $original->default_period_duration,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        foreach ($original->timetableSubjects as $ts) {
            TimetableSubject::create([
                'timetable_id' => $duplicate->id,
                'subject_id' => $ts->subject_id,
                'subject_code' => $ts->subject_code,
                'subject_name' => $ts->subject_name,
                'default_teacher_id' => $ts->default_teacher_id,
                'required_periods_per_week' => $ts->required_periods_per_week,
                'scheduled_periods' => 0,
                'class_type' => $ts->class_type,
            ]);
        }

        Notification::make()->title("Timetable duplicated as '{$newTitle}'")->success()->send();
        $this->redirect(TimetableResource::getUrl('edit', ['record' => $duplicate->id]));
    }
}

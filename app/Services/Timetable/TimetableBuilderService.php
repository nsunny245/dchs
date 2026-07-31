<?php

namespace App\Services\Timetable;

use App\Models\Timetable;
use App\Models\TimetableSubject;
use App\Models\TimetableSlot;
use App\Models\Subject;

class TimetableBuilderService
{
    public static function syncSubjects(Timetable $timetable, array $selectedSubjectIds, array $customTeachers = [], array $customPeriods = []): void
    {
        $existingSubjectIds = $timetable->timetableSubjects()->pluck('subject_id')->filter()->toArray();

        // 1. Remove subjects no longer selected (if no slots attached)
        foreach ($timetable->timetableSubjects as $ttSub) {
            if ($ttSub->subject_id && !in_array($ttSub->subject_id, $selectedSubjectIds)) {
                if ($ttSub->slots()->count() === 0) {
                    $ttSub->delete();
                }
            }
        }

        // 2. Add or update selected subjects
        foreach ($selectedSubjectIds as $subId) {
            $subject = Subject::find($subId);
            if (!$subject) {
                continue;
            }

            $reqPeriods = $customPeriods[$subId] ?? $subject->weekly_periods ?? 4;
            $defaultTeacherId = $customTeachers[$subId] ?? null;

            $ttSubject = TimetableSubject::where('timetable_id', $timetable->id)
                ->where('subject_id', $subId)
                ->first();

            if ($ttSubject) {
                $ttSubject->update([
                    'required_periods_per_week' => $reqPeriods,
                    'default_teacher_id' => $defaultTeacherId ?? $ttSubject->default_teacher_id,
                ]);
            } else {
                TimetableSubject::create([
                    'timetable_id' => $timetable->id,
                    'subject_id' => $subject->id,
                    'subject_code' => $subject->code,
                    'subject_name' => $subject->name,
                    'default_teacher_id' => $defaultTeacherId,
                    'required_periods_per_week' => $reqPeriods,
                    'scheduled_periods' => 0,
                    'class_type' => $subject->default_class_type ?? 'Theory',
                    'is_mandatory' => ($subject->subject_type === 'mandatory'),
                ]);
            }
        }

        self::recalculateScheduledCounts($timetable);
    }

    public static function recalculateScheduledCounts(Timetable $timetable): void
    {
        foreach ($timetable->timetableSubjects as $ttSub) {
            $count = TimetableSlot::where('timetable_id', $timetable->id)
                ->where(function ($q) use ($ttSub) {
                    $q->where('timetable_subject_id', $ttSub->id)
                        ->orWhere('subject_id', $ttSub->subject_id);
                })->count();

            $ttSub->update(['scheduled_periods' => $count]);
        }
    }
}

<?php

namespace App\Services\Timetable;

use App\Models\TimetableSlot;
use App\Models\Staff;
use App\Models\Room;
use App\Models\Timetable;

class TimetableConflictService
{
    public static function validateSlot(array $data, ?int $ignoreSlotId = null): array
    {
        $errors = [];
        $warnings = [];

        $timetableId = $data['timetable_id'] ?? null;
        $dayOfWeek = strtolower($data['day_of_week'] ?? '');
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;
        $teacherId = $data['teacher_id'] ?? null;
        $roomId = $data['room_id'] ?? null;

        if (!$dayOfWeek || !$startTime || !$endTime) {
            return [
                'has_errors' => false,
                'has_warnings' => false,
                'errors' => [],
                'warnings' => [],
            ];
        }

        // 1. Check Teacher Conflict across all published/draft timetables
        if ($teacherId) {
            $teacherConflictQuery = TimetableSlot::where('teacher_id', $teacherId)
                ->where('day_of_week', $dayOfWeek)
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($sub) use ($startTime, $endTime) {
                        $sub->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
                });

            if ($ignoreSlotId) {
                $teacherConflictQuery->where('id', '!=', $ignoreSlotId);
            }

            $conflictingSlot = $teacherConflictQuery->with(['timetable.course', 'timetable.campus', 'room'])->first();

            if ($conflictingSlot) {
                $teacher = Staff::find($teacherId);
                $teacherName = $teacher ? $teacher->full_name : 'Teacher';
                $campusName = $conflictingSlot->timetable->campus->name ?? 'Campus';
                $courseName = $conflictingSlot->timetable->course->name ?? 'Program';
                $section = $conflictingSlot->timetable->section_name ?? '';

                $errors[] = "Teacher Conflict: {$teacherName} is already assigned on {$dayOfWeek} at {$conflictingSlot->start_time}-{$conflictingSlot->end_time} in {$courseName} ({$section}) at {$campusName}.";
            }
        }

        // 2. Check Room Conflict
        if ($roomId) {
            $roomConflictQuery = TimetableSlot::where('room_id', $roomId)
                ->where('day_of_week', $dayOfWeek)
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($sub) use ($startTime, $endTime) {
                        $sub->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
                });

            if ($ignoreSlotId) {
                $roomConflictQuery->where('id', '!=', $ignoreSlotId);
            }

            $conflictingRoomSlot = $roomConflictQuery->with(['timetable.course', 'room'])->first();

            if ($conflictingRoomSlot) {
                $room = Room::find($roomId);
                $roomName = $room ? $room->name : 'Room';

                $errors[] = "Room Conflict: {$roomName} is already occupied on {$dayOfWeek} at {$conflictingRoomSlot->start_time}-{$conflictingRoomSlot->end_time} for {$conflictingRoomSlot->subject_name}.";
            }
        }

        // 3. Check Section Conflict (Same timetable section cannot have 2 classes at same time)
        if ($timetableId) {
            $sectionConflictQuery = TimetableSlot::where('timetable_id', $timetableId)
                ->where('day_of_week', $dayOfWeek)
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($sub) use ($startTime, $endTime) {
                        $sub->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
                });

            if ($ignoreSlotId) {
                $sectionConflictQuery->where('id', '!=', $ignoreSlotId);
            }

            $conflictingSectionSlot = $sectionConflictQuery->first();

            if ($conflictingSectionSlot) {
                $errors[] = "Section Schedule Conflict: Another class ({$conflictingSectionSlot->subject_name}) is already scheduled for this section on {$dayOfWeek} at {$conflictingSectionSlot->start_time}-{$conflictingSectionSlot->end_time}.";
            }
        }

        // 4. Check Teacher Daily & Weekly Workload Warning
        if ($teacherId) {
            $dailyPeriods = TimetableSlot::where('teacher_id', $teacherId)
                ->where('day_of_week', $dayOfWeek)
                ->when($ignoreSlotId, fn($q) => $q->where('id', '!=', $ignoreSlotId))
                ->count();

            if ($dailyPeriods >= 5) {
                $teacher = Staff::find($teacherId);
                $teacherName = $teacher ? $teacher->full_name : 'Teacher';
                $warnings[] = "Teacher Workload Warning: {$teacherName} has {$dailyPeriods} classes scheduled on {$dayOfWeek}.";
            }
        }

        return [
            'has_errors' => count($errors) > 0,
            'has_warnings' => count($warnings) > 0,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}

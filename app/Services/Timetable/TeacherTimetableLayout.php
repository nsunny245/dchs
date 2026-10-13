<?php

namespace App\Services\Timetable;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TeacherTimetableLayout
{
    public const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public static function template(): array
    {
        return json_decode(file_get_contents(resource_path('data/teacher-timetable-template.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function cell(): array
    {
        return ['text' => '', 'days' => []];
    }

    public static function group(): array
    {
        return ['label' => 'New schedule', 'days' => [], 'periods' => [
            ['label' => 'Period 1', 'start' => '08:30', 'end' => '09:10', 'is_break' => false],
        ], 'teachers' => [['name' => '', 'cells' => [self::cell()]]]];
    }

    public static function validate(array $layout): array
    {
        $data = Validator::make(['layout' => $layout], [
            'layout' => 'required|array:heading,subtitle,groups',
            'layout.heading' => 'required|string|max:191',
            'layout.subtitle' => 'required|string|max:191',
            'layout.groups' => 'required|array|min:1|max:14',
            'layout.groups.*' => 'required|array:label,days,periods,teachers',
            'layout.groups.*.label' => 'required|string|max:100',
            'layout.groups.*.days' => 'required|array|min:1|max:7',
            'layout.groups.*.days.*' => 'required|in:'.implode(',', self::DAYS),
            'layout.groups.*.periods' => 'required|array|min:1|max:16',
            'layout.groups.*.periods.*' => 'required|array:label,start,end,is_break',
            'layout.groups.*.periods.*.label' => 'required|string|max:50',
            'layout.groups.*.periods.*.start' => 'required|date_format:H:i',
            'layout.groups.*.periods.*.end' => 'required|date_format:H:i',
            'layout.groups.*.periods.*.is_break' => 'required|boolean',
            'layout.groups.*.teachers' => 'required|array|min:1|max:60',
            'layout.groups.*.teachers.*' => 'required|array:name,cells',
            'layout.groups.*.teachers.*.name' => 'required|string|max:191',
            'layout.groups.*.teachers.*.cells' => 'required|array|min:1|max:16',
            'layout.groups.*.teachers.*.cells.*' => 'required|array:text,days',
            'layout.groups.*.teachers.*.cells.*.text' => 'nullable|string|max:500',
            'layout.groups.*.teachers.*.cells.*.days' => 'present|array|max:7',
            'layout.groups.*.teachers.*.cells.*.days.*' => 'required|in:'.implode(',', self::DAYS),
        ])->validate()['layout'];

        $errors = [];
        $occupied = [];
        foreach ($data['groups'] as $g => $group) {
            $previousEnd = null;
            foreach ($group['periods'] as $p => $period) {
                if ($period['end'] <= $period['start'] || ($previousEnd && $period['start'] < $previousEnd)) {
                    $errors["layout.groups.$g.periods.$p.start"] = 'Periods must run forward without overlapping. Use 24-hour times.';
                }
                $previousEnd = $period['end'];
            }
            $names = [];
            foreach ($group['teachers'] as $r => $teacher) {
                $name = mb_strtolower(trim($teacher['name']));
                if (in_array($name, $names, true)) {
                    $errors["layout.groups.$g.teachers.$r.name"] = 'Use one row per teacher in each schedule.';
                }
                $names[] = $name;
                if (count($teacher['cells']) !== count($group['periods'])) {
                    $errors["layout.groups.$g.teachers.$r.cells"] = 'Every teacher must have one cell per period.';
                }
                foreach ($teacher['cells'] as $p => $cell) {
                    $key = "layout.groups.$g.teachers.$r.cells.$p";
                    if (array_diff($cell['days'], $group['days'])) {
                        $errors[$key.'.days'] = 'A class day must belong to this schedule.';
                    }
                    $period = $group['periods'][$p] ?? null;
                    if (! $period || blank($cell['text'])) {
                        continue;
                    }
                    if ($period['is_break']) {
                        $errors[$key.'.text'] = 'Clear the class before turning its period into a break.';

                        continue;
                    }
                    foreach ($cell['days'] ?: $group['days'] as $day) {
                        foreach ($occupied[$name][$day] ?? [] as $other) {
                            if ($period['start'] < $other['end'] && $period['end'] > $other['start']) {
                                $errors[$key.'.text'] = "{$teacher['name']} has overlapping classes on ".ucfirst($day).'.';
                            }
                        }
                        $occupied[$name][$day][] = $period;
                    }
                }
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    public static function classCount(array $layout): int
    {
        $count = 0;
        foreach ($layout['groups'] ?? [] as $group) {
            foreach ($group['teachers'] as $teacher) {
                foreach ($teacher['cells'] as $cell) {
                    $count += filled($cell['text']) ? count($cell['days'] ?: $group['days']) : 0;
                }
            }
        }

        return $count;
    }
}

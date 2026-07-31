<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Campus;
use App\Models\Subject;
use App\Models\Room;
use App\Models\TimetablePeriod;

class TimetableSetupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Subjects for Courses
        $courses = Course::all();

        foreach ($courses as $course) {
            $codeUpper = strtoupper($course->code ?? 'COURSE');

            if (str_contains($codeUpper, 'LHV') || str_contains($course->name, 'Health Visitor')) {
                $subjects = [
                    ['code' => 'LHV-101', 'name' => 'Anatomy & Physiology', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                    ['code' => 'LHV-102', 'name' => 'Community Health Nursing', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                    ['code' => 'LHV-103', 'name' => 'Maternal & Child Health (MCH)', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                    ['code' => 'LHV-104', 'name' => 'Basic Pharmacology & First Aid', 'semester_year' => 'Year 1', 'credit_hours' => 2, 'weekly_periods' => 3, 'default_class_type' => 'Theory'],
                    ['code' => 'LHV-105', 'name' => 'Clinical Nursing Lab & Demonstration', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Practical'],
                    ['code' => 'LHV-201', 'name' => 'Midwifery & Neonatal Care', 'semester_year' => 'Year 2', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                    ['code' => 'LHV-202', 'name' => 'Community Nutrition & Hygiene', 'semester_year' => 'Year 2', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                ];
            } elseif (str_contains($codeUpper, 'PHARM') || str_contains($course->name, 'Pharmacy')) {
                $subjects = [
                    ['code' => 'PHARM-101', 'name' => 'Pharmaceutics-I (Physical Pharmacy)', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                    ['code' => 'PHARM-102', 'name' => 'Pharmacology & Therapeutics', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                    ['code' => 'PHARM-103', 'name' => 'Pharmacognosy & Phytochemistry', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                    ['code' => 'PHARM-104', 'name' => 'Biochemistry & Microbiology', 'semester_year' => 'Year 1', 'credit_hours' => 2, 'weekly_periods' => 3, 'default_class_type' => 'Theory'],
                    ['code' => 'PHARM-105', 'name' => 'Practical Dispensing & Compounding Lab', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Lab'],
                ];
            } else {
                $subjects = [
                    ['code' => "{$codeUpper}-101", 'name' => 'Fundamentals & Basic Theory', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                    ['code' => "{$codeUpper}-102", 'name' => 'Clinical Diagnostics & Methods', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Theory'],
                    ['code' => "{$codeUpper}-103", 'name' => 'Practical Skill Lab & Practice', 'semester_year' => 'Year 1', 'credit_hours' => 3, 'weekly_periods' => 4, 'default_class_type' => 'Practical'],
                    ['code' => "{$codeUpper}-104", 'name' => 'Medical Ethics & Patient Care', 'semester_year' => 'Year 1', 'credit_hours' => 2, 'weekly_periods' => 3, 'default_class_type' => 'Theory'],
                ];
            }

            foreach ($subjects as $s) {
                Subject::updateOrCreate(
                    ['course_id' => $course->id, 'code' => $s['code']],
                    array_merge($s, ['course_id' => $course->id, 'is_active' => true])
                );
            }
        }

        // 2. Seed Rooms for Campuses
        $campuses = Campus::all();
        foreach ($campuses as $campus) {
            $rooms = [
                ['name' => 'Lecture Hall A', 'code' => 'LH-A', 'room_type' => 'classroom', 'capacity' => 60],
                ['name' => 'Lecture Hall B', 'code' => 'LH-B', 'room_type' => 'classroom', 'capacity' => 60],
                ['name' => 'Anatomy & Clinical Skill Lab', 'code' => 'LAB-1', 'room_type' => 'laboratory', 'capacity' => 40],
                ['name' => 'Computer & Pharmacy Simulation Lab', 'code' => 'LAB-2', 'room_type' => 'laboratory', 'capacity' => 40],
            ];

            foreach ($rooms as $r) {
                Room::updateOrCreate(
                    ['campus_id' => $campus->id, 'name' => $r['name']],
                    array_merge($r, ['campus_id' => $campus->id, 'is_active' => true])
                );
            }
        }

        // 3. Seed Default Global Timetable Periods
        $periods = [
            ['name' => 'Period 1', 'start_time' => '08:30:00', 'end_time' => '09:15:00', 'sort_order' => 1, 'is_break' => false],
            ['name' => 'Period 2', 'start_time' => '09:15:00', 'end_time' => '10:00:00', 'sort_order' => 2, 'is_break' => false],
            ['name' => 'Period 3', 'start_time' => '10:00:00', 'end_time' => '10:45:00', 'sort_order' => 3, 'is_break' => false],
            ['name' => 'Morning Break', 'start_time' => '10:45:00', 'end_time' => '11:15:00', 'sort_order' => 4, 'is_break' => true],
            ['name' => 'Period 4', 'start_time' => '11:15:00', 'end_time' => '12:00:00', 'sort_order' => 5, 'is_break' => false],
            ['name' => 'Period 5', 'start_time' => '12:00:00', 'end_time' => '12:45:00', 'sort_order' => 6, 'is_break' => false],
            ['name' => 'Period 6', 'start_time' => '12:45:00', 'end_time' => '13:30:00', 'sort_order' => 7, 'is_break' => false],
        ];

        foreach ($periods as $p) {
            TimetablePeriod::updateOrCreate(
                ['campus_id' => null, 'name' => $p['name']],
                array_merge($p, ['is_active' => true])
            );
        }
    }
}

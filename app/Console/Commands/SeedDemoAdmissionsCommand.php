<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\AcademicSession;

class SeedDemoAdmissionsCommand extends Command
{
    protected $signature = 'dchs:seed-demo-admissions';
    protected $description = 'Seed demo female LHV admissions across all campuses for staff training';

    public function handle()
    {
        $this->info('Seeding demo female LHV admissions across all campuses...');

        $lhvCourse = Course::where('code', 'LIKE', '%LHV%')
            ->orWhere('name', 'LIKE', '%Lady Health Visitor%')
            ->first();

        if (!$lhvCourse) {
            $lhvCourse = Course::first();
        }

        $session = AcademicSession::first();

        $demos = [
            [
                'campus_name' => 'Okara',
                'applicant_name' => 'Ayesha Khan',
                'cnic' => '35302-1122334-8',
                'dob' => '2004-06-15',
                'phone' => '0300-1122334',
                'email' => 'ayesha.lhv.okara@gmail.com',
                'father_name' => 'Muhammad Ashraf Khan',
                'father_cnic' => '35302-9988776-1',
                'city' => 'Okara',
                'address' => 'House #45, Block A, Scheme 3, Okara',
            ],
            [
                'campus_name' => 'Depalpur',
                'applicant_name' => 'Fatima Bibi',
                'cnic' => '35301-2233445-9',
                'dob' => '2005-02-20',
                'phone' => '0301-2233445',
                'email' => 'fatima.lhv.depalpur@gmail.com',
                'father_name' => 'Tariq Mehmood',
                'father_cnic' => '35301-8877665-2',
                'city' => 'Depalpur',
                'address' => 'Street #3, Near GPO, Main Road, Depalpur',
            ],
            [
                'campus_name' => 'Chichawatni',
                'applicant_name' => 'Zainab Ahmed',
                'cnic' => '31202-3344556-0',
                'dob' => '2004-11-10',
                'phone' => '0302-3344556',
                'email' => 'zainab.lhv.chichawatni@gmail.com',
                'father_name' => 'Ahmed Ali',
                'father_cnic' => '31202-7766554-3',
                'city' => 'Chichawatni',
                'address' => 'Mohallah Farooqia, Railway Road, Chichawatni',
            ],
            [
                'campus_name' => 'Sahiwal',
                'applicant_name' => 'Sana Malik',
                'cnic' => '36502-4455667-1',
                'dob' => '2005-08-05',
                'phone' => '0303-4455667',
                'email' => 'sana.lhv.sahiwal@gmail.com',
                'father_name' => 'Malik Zahid',
                'father_cnic' => '36502-6655443-4',
                'city' => 'Sahiwal',
                'address' => 'House #112, Sector B, Farid Town, Sahiwal',
            ],
        ];

        foreach ($demos as $demo) {
            $campus = Campus::where('name', 'LIKE', '%' . $demo['campus_name'] . '%')->first();
            if (!$campus) continue;

            $admission = Admission::updateOrCreate(
                ['cnic' => $demo['cnic']],
                [
                    'applicant_name' => $demo['applicant_name'],
                    'dob' => $demo['dob'],
                    'gender' => 'female',
                    'phone' => $demo['phone'],
                    'email' => $demo['email'],
                    'city' => $demo['city'],
                    'shift' => 'morning',
                    'reference' => 'Staff Training Demo Entry',
                    'address' => $demo['address'],
                    'father_name' => $demo['father_name'],
                    'father_cnic' => $demo['father_cnic'],
                    'father_occupation' => 'Government Servant',
                    'emergency_contact' => '0344-9988776',
                    'father_address' => $demo['address'],
                    'matric_degree' => 'Matric Science (Biology)',
                    'matric_board' => 'BISE Sahiwal',
                    'matric_year' => '2022',
                    'matric_roll_no' => '405912',
                    'matric_obtained_marks' => 860,
                    'matric_total_marks' => 1100,
                    'matric_grade' => 'A',
                    'matric_biology_marks' => 130,
                    'inter_degree' => 'FSc Pre-Medical',
                    'inter_board' => 'BISE Sahiwal',
                    'inter_year' => '2024',
                    'inter_obtained_marks' => 810,
                    'inter_total_marks' => 1100,
                    'inter_grade' => 'A',
                    'campus_id' => $campus->id,
                    'course_id' => $lhvCourse->id,
                    'academic_session_id' => $session ? $session->id : null,
                    'admission_date' => now(),
                    'status' => 'documents_pending',
                    'missing_documents' => 'Documents are missing and will be uploaded when the student submits it.',
                    'cnic_copy_status' => 'pending',
                    'father_cnic_copy_status' => 'pending',
                    'matric_copy_status' => 'pending',
                    'inter_copy_status' => 'pending',
                    'domicile_copy_status' => 'pending',
                    'character_certificate_copy_status' => 'pending',
                ]
            );

            $this->info("Seeded demo female LHV admission for {$demo['applicant_name']} at {$campus->name} (ID: {$admission->id})");
        }

        $this->info('Demo admissions created successfully!');
        return Command::SUCCESS;
    }
}

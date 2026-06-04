<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campus;
use App\Models\Course;

class CampusAndCourseSeeder extends Seeder
{
    public function run(): void
    {
        // Campuses
        $campuses = [
            [
                'city' => 'Okara',
                'name' => 'Daniyal College Okara',
                'address' => 'Main GT Road, Near 6 Number Chungi, Okara',
                'phone' => '+923217729533',
                'email' => 'info@daniyalgroupofcolleges.com',
                'is_active' => true
            ],
            [
                'city' => 'Depalpur',
                'name' => 'Daniyal College Depalpur',
                'address' => 'Kachery Road, Near Civil Hospital, Depalpur',
                'phone' => '+923217729533',
                'email' => 'info@daniyalgroupofcolleges.com',
                'is_active' => true
            ],
            [
                'city' => 'Chichawatni',
                'name' => 'Daniyal College Chichawatni',
                'address' => 'Bypass Road, Near Grand Feast Marriage Hall, Chichawatni',
                'phone' => '+923217729533',
                'email' => 'info@daniyalgroupofcolleges.com',
                'is_active' => true
            ],
            [
                'city' => 'Sahiwal',
                'name' => 'Daniyal College Sahiwal',
                'address' => 'High Street, Opp. GC University, Sahiwal',
                'phone' => '+923217729533',
                'email' => 'info@daniyalgroupofcolleges.com',
                'is_active' => true
            ]
        ];

        foreach ($campuses as $c) {
            Campus::updateOrCreate(['city' => $c['city']], $c);
        }

        // Courses
        $courses = [
            [
                'code' => 'LHV',
                'name' => 'Lady Health Visitor',
                'duration_months' => 36,
                'eligibility' => 'Matric Science with Physics, Chemistry & Biology with minimum 45% marks.',
                'description' => 'A comprehensive 3-year diploma training female healthcare professionals in maternal care and community nursing.'
            ],
            [
                'code' => 'CMW',
                'name' => 'Community Midwife',
                'duration_months' => 36,
                'eligibility' => 'Matric Science or Arts with minimum 45% marks.',
                'description' => 'Specialized program focused on training midwives to provide safe pregnancy and childbirth services in local communities.'
            ],
            [
                'code' => 'CNA',
                'name' => 'Certified Nursing Assistant',
                'duration_months' => 24,
                'eligibility' => 'Matric Science with 45% marks or Matric Arts with 50% marks.',
                'description' => 'Practical medical training to support registered nurses and provide direct bedside patient care.'
            ],
            [
                'code' => 'PT',
                'name' => 'Pharmacy Technician',
                'duration_months' => 24,
                'eligibility' => 'Matric Science (Biology) with minimum 45% marks.',
                'description' => 'Professional course training assistants for licensed pharmacists in hospital pharmacies and drug stores.'
            ],
            [
                'code' => 'MLT',
                'name' => 'Medical Lab Technology',
                'duration_months' => 24,
                'eligibility' => 'Matric Science with minimum 45% marks.',
                'description' => 'Technical program on performing clinical laboratory diagnostics and analysis of patient samples.'
            ],
            [
                'code' => 'OT',
                'name' => 'Operation Theater Technology',
                'duration_months' => 24,
                'eligibility' => 'Matric Science with minimum 45% marks.',
                'description' => 'Training to assist surgeons, manage surgical equipment, and maintain sterile operation theater environments.'
            ],
            [
                'code' => 'DT',
                'name' => 'Dental Technology',
                'duration_months' => 24,
                'eligibility' => 'Matric Science with minimum 45% marks.',
                'description' => 'Technical study of fabricating dental prosthetics, crowns, bridges, and dental care support.'
            ],
            [
                'code' => 'AT',
                'name' => 'Anesthesia Technology',
                'duration_months' => 24,
                'eligibility' => 'Matric Science with minimum 45% marks.',
                'description' => 'Technical training focused on preparing anesthesia equipment and assisting anesthesiologists in operating theaters.'
            ],
            [
                'code' => 'CSSD',
                'name' => 'Central Sterile Supply Dept',
                'duration_months' => 12,
                'eligibility' => 'Matric Science with minimum 45% marks.',
                'description' => 'A specialized 1-year certificate program focusing on sterilizing, sanitizing, and distributing medical tools and equipment.'
            ],
        ];

        foreach ($courses as $c) {
            Course::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}

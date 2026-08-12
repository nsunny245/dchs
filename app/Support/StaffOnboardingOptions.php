<?php

namespace App\Support;

use App\Models\Campus;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class StaffOnboardingOptions
{
    public static function campuses(): array
    {
        return Cache::remember('staff-onboarding.campuses', now()->addMinutes(10), fn (): array => Campus::query()->orderBy('name')->pluck('name', 'id')->all()
        );
    }

    public static function courses(): array
    {
        return Cache::remember('staff-onboarding.courses', now()->addMinutes(10), fn (): array => Course::query()->orderBy('name')->pluck('name', 'id')->all()
        );
    }

    public static function reportingOfficers(): array
    {
        return Cache::remember('staff-onboarding.reporting-officers', now()->addMinutes(10), fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all()
        );
    }

    public static function highestQualifications(): array
    {
        return [
            'Matric / SSC' => 'Matric / SSC',
            'Intermediate / HSSC' => 'Intermediate / HSSC',
            'Certificate' => 'Professional Certificate',
            'Diploma' => 'Diploma',
            'Associate Degree' => 'Associate Degree',
            'Bachelor' => 'Bachelor Degree',
            'Post RN' => 'Post RN',
            'Master' => 'Master Degree',
            'MPhil' => 'M.Phil / MS',
            'FCPS' => 'FCPS / Fellowship',
            'PhD' => 'Doctorate (PhD)',
            'Other' => 'Other',
        ];
    }

    public static function degreeTitles(): array
    {
        $titles = [
            'Matric / SSC', 'F.Sc. Pre-Medical', 'F.Sc. Pre-Engineering', 'FA', 'ICS', 'I.Com',
            'DAE', 'General Nursing Diploma', 'Midwifery Diploma', 'Lady Health Visitor (LHV)',
            'Community Midwife (CMW)', 'Certified Nursing Assistant (CNA)',
            'Pharmacy Technician Category-B', 'Dispenser Diploma', 'Medical Lab Technician Diploma',
            'Operation Theatre Technician Diploma', 'Radiography / Imaging Technician Diploma',
            'BS Nursing', 'Post RN BS Nursing', 'Pharm-D', 'B.Pharmacy', 'D.Pharmacy',
            'BS Medical Laboratory Technology', 'BS Anesthesia Technology',
            'BS Operation Theatre Technology', 'BS Radiology & Imaging Technology',
            'BS Dental Technology', 'BS Public Health', 'Doctor of Physical Therapy (DPT)',
            'MBBS', 'BDS', 'BS / B.Sc.', 'BA', 'B.Com', 'B.Ed', 'M.Sc.', 'MA', 'M.Com',
            'M.Ed', 'Master of Public Health (MPH)', 'Master of Science in Nursing (MSN)',
            'M.Phil', 'MS', 'FCPS', 'PhD', 'Other / Not Listed',
        ];

        return array_combine($titles, $titles);
    }

    public static function specializations(): array
    {
        $items = [
            'Nursing', 'Community Health Nursing', 'Medical-Surgical Nursing', 'Pediatric Nursing',
            'Mental Health Nursing', 'Midwifery', 'Pharmacy', 'Pharmacology', 'Pharmaceutics',
            'Clinical Pharmacy', 'Pharmaceutical Chemistry', 'Medical Laboratory Technology',
            'Anesthesia Technology', 'Operation Theatre Technology', 'Radiology & Imaging',
            'Dental Technology', 'Public Health', 'Physical Therapy', 'Anatomy', 'Physiology',
            'Pathology', 'Microbiology', 'Biochemistry', 'Community Medicine', 'Medicine', 'Surgery',
            'English', 'Information Technology / Computer Science', 'Mathematics', 'Education',
            'Administration', 'Finance / Accounting', 'Human Resources', 'Other / Not Listed',
        ];

        return array_combine($items, $items);
    }

    public static function institutions(): array
    {
        $items = [
            'University of the Punjab', 'University of Health Sciences Lahore',
            'King Edward Medical University', 'Allama Iqbal Medical College',
            'Fatima Jinnah Medical University', 'The University of Lahore',
            'University of Central Punjab', 'Superior University', 'Lahore College for Women University',
            'Government College University Lahore', 'Government College University Faisalabad',
            'University of Agriculture Faisalabad', 'University of Sargodha',
            'Bahauddin Zakariya University', 'The Islamia University of Bahawalpur',
            'Nishtar Medical University', 'Rawalpindi Medical University',
            'Quaid-i-Azam University', 'COMSATS University Islamabad',
            'National University of Sciences and Technology', 'Riphah International University',
            'Shifa Tameer-e-Millat University', 'Aga Khan University', 'University of Karachi',
            'Dow University of Health Sciences', 'Jinnah Sindh Medical University',
            'Liaquat University of Medical & Health Sciences', 'Khyber Medical University',
            'University of Peshawar', 'Shaheed Zulfiqar Ali Bhutto Medical University',
            'Virtual University of Pakistan', 'Allama Iqbal Open University',
            'Punjab Medical Faculty', 'Pakistan Nursing & Midwifery Council Recognized Institute',
            'Pharmacy Council of Pakistan Recognized Institute', 'Other / Not Listed',
        ];

        return array_combine($items, $items);
    }

    public static function passingYears(): array
    {
        $years = range((int) date('Y'), 1950);

        return array_combine($years, $years);
    }

    public static function experienceYears(): array
    {
        $options = ['0' => 'No experience', '0.5' => 'Less than 1 year'];

        for ($year = 1; $year <= 50; $year++) {
            $options[(string) $year] = $year.($year === 1 ? ' year' : ' years');
        }

        return $options;
    }

    public static function documentTypes(): array
    {
        return [
            'cnic_front' => 'CNIC Front',
            'cnic_back' => 'CNIC Back',
            'degree' => 'Educational Degree',
            'transcript' => 'Educational Transcript',
            'cv' => 'Curriculum Vitae (CV)',
            'experience' => 'Experience Letter',
        ];
    }
}

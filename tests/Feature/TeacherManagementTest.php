<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Course;
use App\Models\Staff;
use App\Models\User;
use App\Models\EmploymentRecord;
use App\Models\SalaryRecord;
use App\Models\StaffDocument;
use App\Services\HR\GenerateEmployeeIdService;
use App\Services\HR\CalculateProfileCompletionService;
use App\Services\HR\EvaluateAgreementReadinessService;
use App\Services\HR\GenerateTeacherAgreementAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'StaffPermissionsSeeder']);
    }

    public function test_employee_id_is_generated_with_unique_sequence()
    {
        $campus = Campus::create([
            'name' => 'Okara Campus',
            'code' => 'OKR',
            'city' => 'Okara',
            'address' => 'Okara Address',
            'phone' => '03211234567',
            'is_active' => true,
        ]);
        $campus->code = 'OKR';
        $campus->save();

        $id1 = GenerateEmployeeIdService::generate($campus->id, 'TEA');
        $this->assertEquals('DGC-OKR-TEA-0001', $id1);

        Staff::create([
            'campus_id' => $campus->id,
            'employee_id' => $id1,
            'full_name' => 'Teacher One',
            'cnic' => '38403-1111111-1',
            'phone' => '03211111111',
            'designation' => 'Lecturer',
            'hire_date' => now(),
            'joining_date' => now(),
            'emergency_contact_name' => 'Father',
            'emergency_contact_relationship' => 'Father',
            'emergency_contact_phone' => '03219999999',
        ]);

        $id2 = GenerateEmployeeIdService::generate($campus->id, 'TEA');
        $this->assertEquals('DGC-OKR-TEA-0002', $id2);
    }

    public function test_profile_completion_service_calculates_percentage()
    {
        $campus = Campus::create([
            'name' => 'Sahiwal Campus',
            'code' => 'SWL',
            'city' => 'Sahiwal',
            'address' => 'Sahiwal Address',
            'phone' => '03211234568',
            'is_active' => true,
        ]);

        $staff = Staff::create([
            'campus_id' => $campus->id,
            'employee_id' => 'DGC-SWL-TEA-0001',
            'full_name' => 'Dr. Ahmad Khan',
            'cnic' => '38403-2222222-2',
            'phone' => '03212222222',
            'designation' => 'Assistant Professor',
            'hire_date' => now(),
            'joining_date' => now(),
            'emergency_contact_name' => 'Spouse',
            'emergency_contact_relationship' => 'Spouse',
            'emergency_contact_phone' => '03218888888',
        ]);

        $result = CalculateProfileCompletionService::evaluate($staff);
        $this->assertIsInt($result['percentage']);
        $this->assertGreaterThan(0, $result['percentage']);
    }

    public function test_agreement_readiness_blocks_when_mandatory_data_missing()
    {
        $campus = Campus::create([
            'name' => 'Depalpur Campus',
            'code' => 'DPL',
            'city' => 'Depalpur',
            'address' => 'Depalpur Address',
            'phone' => '03211234569',
            'is_active' => true,
        ]);

        $staff = Staff::create([
            'campus_id' => $campus->id,
            'employee_id' => 'DGC-DPL-TEA-0001',
            'full_name' => 'Incomplete Teacher',
            'cnic' => '38403-3333333-3',
            'phone' => '03213333333',
            'designation' => 'Lecturer',
            'hire_date' => now(),
            'joining_date' => now(),
            'emergency_contact_name' => 'Emergency',
            'emergency_contact_relationship' => 'Brother',
            'emergency_contact_phone' => '03217777777',
        ]);

        $readiness = EvaluateAgreementReadinessService::check($staff);
        $this->assertFalse($readiness['is_ready']);
        $this->assertNotEmpty($readiness['reasons']);
    }

    public function test_generate_agreement_action_creates_pdf_version()
    {
        $campus = Campus::create([
            'name' => 'Chichawatni Campus',
            'code' => 'CHW',
            'city' => 'Chichawatni',
            'address' => 'Chichawatni Address',
            'phone' => '03211234570',
            'is_active' => true,
        ]);

        $staff = Staff::create([
            'campus_id' => $campus->id,
            'employee_id' => 'DGC-CHW-TEA-0001',
            'full_name' => 'Prof. Tariq Mahmood',
            'cnic' => '38403-4444444-4',
            'phone' => '03214444444',
            'designation' => 'Professor',
            'hire_date' => now(),
            'joining_date' => now(),
            'emergency_contact_name' => 'Spouse',
            'emergency_contact_relationship' => 'Spouse',
            'emergency_contact_phone' => '03216666666',
        ]);

        EmploymentRecord::create([
            'staff_id' => $staff->id,
            'campus_id' => $campus->id,
            'designation' => 'Professor',
            'appointment_status' => 'permanent',
            'joining_date' => now(),
            'is_current' => true,
        ]);

        SalaryRecord::create([
            'staff_id' => $staff->id,
            'gross_salary' => 120000.00,
            'basic_salary' => 80000.00,
            'status' => 'approved',
        ]);

        StaffDocument::create([
            'staff_id' => $staff->id,
            'document_type' => 'cnic',
            'title' => 'CNIC Copy',
            'path' => 'staff/documents/cnic/test_cnic.pdf',
            'status' => 'verified',
        ]);

        $agreement = GenerateTeacherAgreementAction::execute($staff);

        $this->assertNotNull($agreement);
        $this->assertEquals(1, $agreement->version);
        $this->assertStringContainsString('DGC-CHW-TEA-0001', $agreement->agreement_number);
    }
}

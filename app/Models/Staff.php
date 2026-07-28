<?php

namespace App\Models;

use App\Traits\ScopedByCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Staff extends Model
{
    use ScopedByCampus;

    protected $guarded = [];

    protected $casts = [
        'hire_date' => 'date',
        'joining_date' => 'date',
        'cnic_issue_date' => 'date',
        'cnic_expiry_date' => 'date',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function academics(): HasOne
    {
        return $this->hasOne(TeacherAcademic::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ProfessionalRegistration::class);
    }

    public function programmes(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'teacher_programmes', 'staff_id', 'course_id');
    }

    public function employmentRecords(): HasMany
    {
        return $this->hasMany(EmploymentRecord::class)->orderBy('effective_from', 'desc');
    }

    public function currentEmployment(): HasOne
    {
        return $this->hasOne(EmploymentRecord::class)->where('is_current', true)->latestOfMany();
    }

    public function salaryRecords(): HasMany
    {
        return $this->hasMany(SalaryRecord::class)->orderBy('effective_from', 'desc');
    }

    public function currentSalary(): HasOne
    {
        return $this->hasOne(SalaryRecord::class)->where('status', 'approved')->latestOfMany();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StaffDocument::class)->orderBy('created_at', 'desc');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class)->orderBy('start_date', 'desc');
    }

    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class)->orderBy('attendance_date', 'desc');
    }

    public function agreementVersions(): HasMany
    {
        return $this->hasMany(AgreementVersion::class)->orderBy('version', 'desc');
    }

    public function currentAgreement(): HasOne
    {
        return $this->hasOne(AgreementVersion::class)->latestOfMany();
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Franchisor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function studentPayments()
    {
        return $this->hasMany(FranchisorStudentPayment::class);
    }

    public function courseDeals()
    {
        return $this->hasMany(FranchisorCourseDeal::class);
    }
}

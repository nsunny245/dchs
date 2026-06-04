<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['code', 'name', 'duration_months', 'eligibility', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

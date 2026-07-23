<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLedgerEntry extends Model
{
    protected $guarded = [];

    protected $casts = ['posted_at' => 'datetime', 'metadata' => 'array'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_category_id',
        'campus_id',
        'title',
        'amount',
        'expense_date',
        'notes',
        'receipt',
        'expense_source',
        'college_revenue_amount',
        'chairman_naveed_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'college_revenue_amount' => 'decimal:2',
        'chairman_naveed_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function (Expense $expense) {
            if ($expense->expense_source === 'college_revenue') {
                $expense->college_revenue_amount = $expense->amount;
                $expense->chairman_naveed_amount = 0.00;
            } elseif ($expense->expense_source === 'chairman_naveed') {
                $expense->college_revenue_amount = 0.00;
                $expense->chairman_naveed_amount = $expense->amount;
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}

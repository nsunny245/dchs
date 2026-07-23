<?php

namespace App\Models;

use App\Traits\ScopedByCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Admission extends Model
{
    use ScopedByCampus;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
        'applied_at' => 'datetime',
        'academic_details' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($admission) {
            if ($admission->academic_details && is_array($admission->academic_details)) {
                // Reset flat fields first
                $admission->matric_degree = null;
                $admission->matric_board = null;
                $admission->matric_year = null;
                $admission->matric_roll_no = null;
                $admission->matric_obtained_marks = null;
                $admission->matric_total_marks = null;
                $admission->matric_grade = null;
                $admission->matric_biology_marks = null;

                $admission->inter_degree = null;
                $admission->inter_board = null;
                $admission->inter_year = null;
                $admission->inter_roll_no = null;
                $admission->inter_obtained_marks = null;
                $admission->inter_total_marks = null;
                $admission->inter_grade = null;

                $admission->grad_degree = null;
                $admission->grad_board = null;
                $admission->grad_year = null;
                $admission->grad_roll_no = null;
                $admission->grad_obtained_marks = null;
                $admission->grad_total_marks = null;
                $admission->grad_grade = null;
                
                foreach ($admission->academic_details as $record) {
                    if (($record['level'] ?? '') === 'matric') {
                        $admission->matric_degree = $record['degree_title'] ?? null;
                        $admission->matric_board = $record['board_university'] ?? null;
                        $admission->matric_year = $record['passing_year'] ?? null;
                        $admission->matric_roll_no = $record['roll_no'] ?? null;
                        $admission->matric_obtained_marks = $record['obtained_marks'] ?? null;
                        $admission->matric_total_marks = $record['total_marks'] ?? null;
                        $admission->matric_grade = $record['grade'] ?? null;
                        $admission->matric_biology_marks = $record['biology_marks'] ?? null;
                    }
                    if (($record['level'] ?? '') === 'intermediate') {
                        $admission->inter_degree = $record['degree_title'] ?? null;
                        $admission->inter_board = $record['board_university'] ?? null;
                        $admission->inter_year = $record['passing_year'] ?? null;
                        $admission->inter_roll_no = $record['roll_no'] ?? null;
                        $admission->inter_obtained_marks = $record['obtained_marks'] ?? null;
                        $admission->inter_total_marks = $record['total_marks'] ?? null;
                        $admission->inter_grade = $record['grade'] ?? null;
                    }
                    if (($record['level'] ?? '') === 'graduation') {
                        $admission->grad_degree = $record['degree_title'] ?? null;
                        $admission->grad_board = $record['board_university'] ?? null;
                        $admission->grad_year = $record['passing_year'] ?? null;
                        $admission->grad_roll_no = $record['roll_no'] ?? null;
                        $admission->grad_obtained_marks = $record['obtained_marks'] ?? null;
                        $admission->grad_total_marks = $record['total_marks'] ?? null;
                        $admission->grad_grade = $record['grade'] ?? null;
                    }
                }
            }
        });

        static::retrieved(function ($admission) {
            if (empty($admission->academic_details)) {
                $details = [];
                if ($admission->matric_degree) {
                    $details[] = [
                        'level' => 'matric',
                        'degree_title' => $admission->matric_degree,
                        'board_university' => $admission->matric_board,
                        'passing_year' => $admission->matric_year,
                        'roll_no' => $admission->matric_roll_no,
                        'obtained_marks' => $admission->matric_obtained_marks,
                        'total_marks' => $admission->matric_total_marks,
                        'grade' => $admission->matric_grade,
                        'biology_marks' => $admission->matric_biology_marks,
                    ];
                }
                if ($admission->inter_degree) {
                    $details[] = [
                        'level' => 'intermediate',
                        'degree_title' => $admission->inter_degree,
                        'board_university' => $admission->inter_board,
                        'passing_year' => $admission->inter_year,
                        'roll_no' => $admission->inter_roll_no,
                        'obtained_marks' => $admission->inter_obtained_marks,
                        'total_marks' => $admission->inter_total_marks,
                        'grade' => $admission->inter_grade,
                    ];
                }
                if ($admission->grad_degree) {
                    $details[] = [
                        'level' => 'graduation',
                        'degree_title' => $admission->grad_degree,
                        'board_university' => $admission->grad_board,
                        'passing_year' => $admission->grad_year,
                        'roll_no' => $admission->grad_roll_no,
                        'obtained_marks' => $admission->grad_obtained_marks,
                        'total_marks' => $admission->grad_total_marks,
                        'grade' => $admission->grad_grade,
                    ];
                }
                if (count($details) > 0) {
                    $admission->academic_details = $details;
                }
            }
        });
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function franchisor(): BelongsTo
    {
        return $this->belongsTo(Franchisor::class);
    }

    public function franchisorPayment(): HasOne
    {
        return $this->hasOne(FranchisorStudentPayment::class);
    }

    public function visitorQuery(): BelongsTo
    {
        return $this->belongsTo(VisitorQuery::class, 'visitor_query_id');
    }
}

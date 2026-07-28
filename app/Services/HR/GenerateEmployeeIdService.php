<?php

namespace App\Services\HR;

use App\Models\Campus;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class GenerateEmployeeIdService
{
    public static function generate(?int $campusId, string $category = 'TEA'): string
    {
        return DB::transaction(function () use ($campusId, $category) {
            $campusCode = 'MAIN';
            if ($campusId) {
                $campus = Campus::find($campusId);
                if ($campus && !empty($campus->code)) {
                    $campusCode = strtoupper($campus->code);
                } else if ($campus) {
                    $campusCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $campus->name), 0, 3));
                }
            }

            $catCode = strtoupper(substr($category, 0, 3));
            $prefix = "DGC-{$campusCode}-{$catCode}-";

            $maxSeq = Staff::withoutGlobalScopes()
                ->where('employee_id', 'LIKE', "{$prefix}%")
                ->get()
                ->map(function ($staff) use ($prefix) {
                    $seqPart = str_replace($prefix, '', $staff->employee_id);
                    return is_numeric($seqPart) ? (int)$seqPart : 0;
                })
                ->max() ?? 0;

            $nextSeq = str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);

            return "{$prefix}{$nextSeq}";
        });
    }
}

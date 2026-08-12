<?php

namespace App\Services\HR;

use App\Models\Campus;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class GenerateEmployeeIdService
{
    public static function generate(?int $campusId, string $category = 'TEA'): string
    {
        $campusCode = 'MAIN';
        if ($campusId) {
            $campus = Campus::query()->find($campusId, ['id', 'name', 'code']);
            if ($campus && ! empty($campus->code)) {
                $campusCode = strtoupper($campus->code);
            } elseif ($campus) {
                $campusCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $campus->name), 0, 3));
            }
        }

        $catCode = strtoupper(substr($category, 0, 3));
        $prefix = "DGC-{$campusCode}-{$catCode}-";

        $sequenceStart = strlen($prefix) + 1;
        $sequenceExpression = DB::getDriverName() === 'sqlite'
            ? "CAST(SUBSTR(employee_id, {$sequenceStart}) AS INTEGER)"
            : "CAST(SUBSTRING(employee_id, {$sequenceStart}) AS UNSIGNED)";

        $sequence = (int) (Staff::withoutGlobalScopes()
            ->where('employee_id', 'like', "{$prefix}%")
            ->max(DB::raw($sequenceExpression)) ?? 0);
        $nextSeq = str_pad($sequence + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$nextSeq}";
    }
}

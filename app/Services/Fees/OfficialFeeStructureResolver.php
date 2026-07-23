<?php

namespace App\Services\Fees;

use App\Models\FeeStructure;
use Carbon\CarbonInterface;

class OfficialFeeStructureResolver
{
    public function resolve(
        int $courseId,
        ?int $campusId,
        ?int $academicSessionId,
        CarbonInterface|string|null $effectiveOn = null,
    ): ?FeeStructure {
        $effectiveOn ??= now();

        return FeeStructure::query()
            ->where('course_id', $courseId)
            ->where(fn ($query) => $query
                ->whereNull('campus_id')
                ->orWhere('campus_id', $campusId))
            ->where(fn ($query) => $query
                ->whereNull('academic_session_id')
                ->orWhere('academic_session_id', $academicSessionId))
            ->where('status', 'active')
            ->where(fn ($query) => $query
                ->whereNull('effective_date')
                ->orWhereDate('effective_date', '<=', $effectiveOn))
            ->where(fn ($query) => $query
                ->whereNull('expiry_date')
                ->orWhereDate('expiry_date', '>=', $effectiveOn))
            ->orderByRaw('campus_id IS NULL')
            ->orderByRaw('academic_session_id IS NULL')
            ->latest('version')
            ->first();
    }
}

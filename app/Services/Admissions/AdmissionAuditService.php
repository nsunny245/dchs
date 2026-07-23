<?php

namespace App\Services\Admissions;

use App\Models\AdmissionAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdmissionAuditService
{
    public function record(Model $subject, string $action, array $old = [], array $new = [], ?string $notes = null): void
    {
        AdmissionAuditLog::create([
            'request_uuid' => request()->attributes->get('request_uuid', (string) Str::uuid()),
            'user_id' => auth()->id(),
            'campus_id' => $subject->campus_id ?? auth()->user()?->campus_id,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()->ip(),
            'notes' => $notes,
        ]);
    }
}

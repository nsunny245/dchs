<?php

namespace App\Services\Admissions;

use App\Models\AdmissionDraft;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdmissionDraftService
{
    public function save(array $payload, User $user, ?string $uuid = null, int $step = 1): AdmissionDraft
    {
        $isNew = ! $uuid;
        $draft = $uuid
            ? AdmissionDraft::where('uuid', $uuid)->where('created_by', $user->id)->firstOrFail()
            : new AdmissionDraft(['uuid' => (string) Str::uuid(), 'created_by' => $user->id]);

        $sanitized = $this->sanitize($payload);

        if ($draft->exists && $draft->payload === $sanitized && $draft->current_step === max(1, min(7, $step))) {
            $draft->updateQuietly(['last_saved_at' => now(), 'updated_by' => $user->id]);

            return $draft;
        }

        $draft->fill([
            'updated_by' => $user->id,
            'campus_id' => Arr::get($payload, 'campus_id', $user->campus_id),
            'current_step' => max(1, min(7, $step)),
            'version' => $draft->exists ? $draft->version + 1 : 1,
            'status' => 'draft',
            'payload' => $sanitized,
            'last_saved_at' => now(),
        ])->save();

        app(AdmissionAuditService::class)->record(
            $draft,
            $isNew ? 'admission_draft_created' : 'admission_draft_edited',
            [],
            ['current_step' => $draft->current_step, 'version' => $draft->version],
        );

        return $draft;
    }

    private function sanitize(array $payload): array
    {
        $payload = Arr::except($payload, ['password', 'declaration_accepted']);

        array_walk_recursive($payload, function (&$value): void {
            if (is_object($value)) {
                $value = method_exists($value, '__toString') ? (string) $value : null;
            }
        });

        return $payload;
    }
}

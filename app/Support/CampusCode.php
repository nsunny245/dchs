<?php

namespace App\Support;

use App\Models\Campus;

class CampusCode
{
    public static function for(?Campus $campus): string
    {
        if (! $campus) {
            return 'GEN';
        }

        $explicitCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $campus->code));

        $explicitCode = preg_replace('/^DGC/', '', $explicitCode);

        if ($explicitCode !== '' && $explicitCode !== 'DAN') {
            return substr($explicitCode, 0, 6);
        }

        $words = preg_split('/[^A-Z0-9]+/i', (string) $campus->name, -1, PREG_SPLIT_NO_EMPTY);
        $ignored = ['DANIYAL', 'GROUP', 'OF', 'COLLEGE', 'COLLEGES', 'CAMPUS', 'HEALTH', 'SCIENCES'];
        $meaningful = array_values(array_filter(
            $words ?: [],
            fn (string $word): bool => ! in_array(strtoupper($word), $ignored, true)
        ));
        $source = $meaningful[0] ?? ($words[0] ?? (string) $campus->city ?: 'GEN');
        $code = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $source), 0, 3));

        return $code !== '' ? $code : 'C'.str_pad((string) $campus->id, 2, '0', STR_PAD_LEFT);
    }
}

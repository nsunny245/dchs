<?php

namespace App\Models;

use App\Traits\ScopedByCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use ScopedByCampus;

    protected $guarded = [];

    protected $casts = [
        'value' => 'json',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public static function getGlobal(string $key, $default = null)
    {
        $setting = self::withoutGlobalScopes()
            ->whereNull('campus_id')
            ->where('key', $key)
            ->first();

        if ($setting) {
            return $setting->value;
        }

        return $default;
    }
}

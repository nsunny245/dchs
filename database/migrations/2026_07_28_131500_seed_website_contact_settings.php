<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::withoutGlobalScopes()->updateOrCreate(
            ['campus_id' => null, 'key' => 'website_phone'],
            ['value' => json_encode('+92 321-7729533'), 'type' => 'string', 'group' => 'website']
        );

        Setting::withoutGlobalScopes()->updateOrCreate(
            ['campus_id' => null, 'key' => 'website_email'],
            ['value' => json_encode('info@daniyalgroupofcolleges.com'), 'type' => 'string', 'group' => 'website']
        );

        Setting::withoutGlobalScopes()->updateOrCreate(
            ['campus_id' => null, 'key' => 'website_address'],
            ['value' => json_encode('Okara · Sahiwal · Depalpur · Chichawatni (Punjab)'), 'type' => 'string', 'group' => 'website']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::withoutGlobalScopes()
            ->whereNull('campus_id')
            ->whereIn('key', ['website_phone', 'website_email', 'website_address'])
            ->delete();
    }
};

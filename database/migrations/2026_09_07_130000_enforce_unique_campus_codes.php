<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $used = [];

        DB::table('campuses')->orderBy('id')->get()->each(function (object $campus) use (&$used): void {
            $source = trim((string) ($campus->city ?: $campus->name));
            $base = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $source), 0, 3)) ?: 'CMP';
            $candidate = 'DGC-'.$base;
            $suffix = 2;

            while (isset($used[$candidate])) {
                $candidate = 'DGC-'.$base.'-'.$suffix++;
            }

            $used[$candidate] = true;
            DB::table('campuses')->where('id', $campus->id)->update(['code' => $candidate]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE campuses MODIFY code VARCHAR(32) NOT NULL');
        }

        Schema::table('campuses', function (Blueprint $table): void {
            $table->unique('code', 'campuses_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table): void {
            $table->dropUnique('campuses_code_unique');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE campuses MODIFY code VARCHAR(255) NULL');
        }
    }
};

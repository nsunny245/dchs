<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_vouchers', 'edit_request_status')) {
                $table->string('edit_request_status')->nullable()->after('status');
                $table->text('edit_request_reason')->nullable()->after('edit_request_status');
                $table->unsignedBigInteger('edit_requested_by')->nullable()->after('edit_request_reason');

                $table->foreign('edit_requested_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('fee_vouchers', 'edit_request_status')) {
                $table->dropForeign(['edit_requested_by']);
                $table->dropColumn(['edit_request_status', 'edit_request_reason', 'edit_requested_by']);
            }
        });
    }
};

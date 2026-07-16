<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            // Change status from enum to string to allow 'document_missing' and other flexible statuses
            $table->string('status')->default('pending')->change();
            
            if (!Schema::hasColumn('admissions', 'documents_zip_path')) {
                $table->string('documents_zip_path')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'missing_documents')) {
                $table->text('missing_documents')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (Schema::hasColumn('admissions', 'documents_zip_path')) {
                $table->dropColumn('documents_zip_path');
            }
            if (Schema::hasColumn('admissions', 'missing_documents')) {
                $table->dropColumn('missing_documents');
            }
        });
    }
};

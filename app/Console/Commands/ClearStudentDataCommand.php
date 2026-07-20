<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearStudentDataCommand extends Command
{
    protected $signature = 'dchs:clear-student-data';
    protected $description = 'Clear all admissions and student records across all campuses';

    public function handle()
    {
        $this->info('Starting student and admission data cleanup...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            'payment_allocations',
            'payments',
            'student_vouchers',
            'student_fee_snapshots',
            'student_fee_accounts',
            'student_documents',
            'concessions',
            'fee_payments',
            'students',
            'admissions',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->info("Truncated table: {$table}");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Successfully wiped all student and admission records across all campuses.');
        return Command::SUCCESS;
    }
}

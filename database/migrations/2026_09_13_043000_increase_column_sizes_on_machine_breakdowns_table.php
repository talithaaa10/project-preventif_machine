<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $statements = [
            "ALTER TABLE `machine_breakdowns` MODIFY `duration` DECIMAL(20, 4) NULL",
            "ALTER TABLE `machine_breakdowns` MODIFY `target` DECIMAL(20, 4) NULL",
            "ALTER TABLE `machine_breakdowns` MODIFY `frequency` DECIMAL(20, 4) NULL",
            "ALTER TABLE `machine_breakdowns` MODIFY `persen` DECIMAL(20, 4) NULL",
        ];

        foreach ($statements as $sql) {
            try {
                \Illuminate\Support\Facades\DB::statement($sql);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $statements = [
            "ALTER TABLE `machine_breakdowns` MODIFY `duration` DECIMAL(12, 2) NULL",
            "ALTER TABLE `machine_breakdowns` MODIFY `target` DECIMAL(12, 4) NULL",
            "ALTER TABLE `machine_breakdowns` MODIFY `frequency` DECIMAL(12, 2) NULL",
            "ALTER TABLE `machine_breakdowns` MODIFY `persen` DECIMAL(12, 2) NULL",
        ];

        foreach ($statements as $sql) {
            try {
                \Illuminate\Support\Facades\DB::statement($sql);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
};

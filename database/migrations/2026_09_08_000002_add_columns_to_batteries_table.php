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
        // Some MySQL versions don't expose 'generation_expression' in information_schema
        // which causes Schema::hasColumn to fail. Use raw ALTER statements with try/catch
        $statements = [
            "ALTER TABLE `batteries` ADD COLUMN `level` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `battery_id` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `area` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `line` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `machine_no` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `machine_name` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `maker` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `equipment_type` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `device` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `battery_model` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `battery_type` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `std_volt` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `install_date` DATE NULL",
            "ALTER TABLE `batteries` ADD COLUMN `replacement_cycle_month` INT NULL",
            "ALTER TABLE `batteries` ADD COLUMN `next_replace_date` DATE NULL",
            "ALTER TABLE `batteries` ADD COLUMN `status_aktif` VARCHAR(255) NULL",
        ];

        foreach ($statements as $sql) {
            try {
                \Illuminate\Support\Facades\DB::statement($sql);
            } catch (\Throwable $e) {
                // ignore errors (most likely column already exists)
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batteries', function (Blueprint $table) {
            $cols = [
                'level', 'battery_id', 'area', 'line', 'machine_no', 'machine_name', 'maker', 'equipment_type', 'device', 'battery_model', 'battery_type', 'std_volt', 'install_date', 'replacement_cycle_month', 'next_replace_date', 'status_aktif'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('batteries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

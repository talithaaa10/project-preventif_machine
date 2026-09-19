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
            "ALTER TABLE `batteries` ADD COLUMN `op_number` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `exchange_type` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `trend_pengganti` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `how_many` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `number_of` VARCHAR(255) NULL",
            "ALTER TABLE `batteries` ADD COLUMN `aggregate` VARCHAR(255) NULL",
        ];

        foreach ($statements as $sql) {
            try {
                \Illuminate\Support\Facades\DB::statement($sql);
            } catch (\Throwable $e) {
                // ignore if column already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batteries', function (Blueprint $table) {
            $cols = ['op_number', 'exchange_type', 'trend_pengganti', 'how_many', 'number_of', 'aggregate'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('batteries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

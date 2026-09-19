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
        Schema::table('input_batteries', function (Blueprint $table) {
            if (!Schema::hasColumn('input_batteries', 'prev_install_date')) {
                $table->date('prev_install_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('input_batteries', 'prev_next_replace_date')) {
                $table->date('prev_next_replace_date')->nullable()->after('prev_install_date');
            }
            if (!Schema::hasColumn('input_batteries', 'prev_status_aktif')) {
                $table->string('prev_status_aktif', 50)->nullable()->after('prev_next_replace_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('input_batteries', function (Blueprint $table) {
            $cols = ['prev_install_date', 'prev_next_replace_date', 'prev_status_aktif'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('input_batteries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

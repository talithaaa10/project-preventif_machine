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
            if (!Schema::hasColumn('input_batteries', 'area')) {
                $table->string('area')->nullable()->after('battery_id');
            }
            if (!Schema::hasColumn('input_batteries', 'voltage_before')) {
                $table->string('voltage_before', 50)->nullable()->after('exchange_type');
            }
            if (!Schema::hasColumn('input_batteries', 'voltage_after')) {
                $table->string('voltage_after', 50)->nullable()->after('voltage_before');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('input_batteries', function (Blueprint $table) {
            $cols = ['area', 'voltage_before', 'voltage_after'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('input_batteries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

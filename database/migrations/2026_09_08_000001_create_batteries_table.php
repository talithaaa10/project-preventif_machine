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
        Schema::create('batteries', function (Blueprint $table) {
            $table->id();
            $table->string('level')->nullable();
            $table->string('battery_id')->nullable();
            $table->string('area')->nullable();
            $table->string('line')->nullable();
            $table->string('machine_no')->nullable();
            $table->string('machine_name')->nullable();
            $table->string('maker')->nullable();
            $table->string('equipment_type')->nullable();
            $table->string('device')->nullable();
            $table->string('battery_model')->nullable();
            $table->string('battery_type')->nullable();
            $table->string('std_volt')->nullable();
            $table->date('install_date')->nullable();
            $table->integer('replacement_cycle_month')->nullable();
            $table->date('next_replace_date')->nullable();
            $table->string('status_aktif')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batteries');
    }
};

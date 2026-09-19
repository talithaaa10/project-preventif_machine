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
        Schema::create('input_batteries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('battery_id')->nullable()->index();
            $table->string('line')->nullable();
            $table->string('op_number')->nullable();
            $table->string('machine_no')->nullable();
            $table->string('equipment_type')->nullable();
            $table->string('battery_model')->nullable();
            $table->string('exchange_type')->nullable();
            $table->date('last_day')->nullable();
            $table->date('trend_pengganti')->nullable();
            $table->string('standart_volt')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            // Foreign key to batteries table (nullable if not yet matched)
            $table->foreign('battery_id')->references('id')->on('batteries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('input_batteries');
    }
};

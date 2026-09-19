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
        Schema::create('machine_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->string('cat')->nullable();
            $table->date('date')->nullable();
            $table->string('status')->nullable();
            $table->string('kpi')->nullable();
            $table->string('sub_kp')->nullable();
            $table->string('line')->nullable();
            $table->decimal('target', 12, 4)->nullable();
            $table->decimal('duration', 12, 2)->nullable();
            $table->decimal('frequency', 12, 2)->nullable();
            $table->decimal('persen', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_breakdowns');
    }
};

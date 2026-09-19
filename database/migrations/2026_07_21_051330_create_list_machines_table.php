<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('hmmi')->nullable();
            $table->string('op_no')->nullable();
            $table->string('plant')->nullable();
            $table->string('line')->nullable();
            $table->string('product')->nullable();
            $table->string('fungtion')->nullable();
            $table->string('category')->nullable();
            $table->string('mc_category')->nullable();
            $table->string('maker')->nullable();
            $table->string('model_type')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('year')->nullable();
            $table->string('contact')->nullable();
            $table->string('machine_made')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_machines');
    }
};

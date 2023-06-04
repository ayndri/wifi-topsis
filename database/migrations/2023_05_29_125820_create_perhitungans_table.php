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
        Schema::create('perhitungans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_id')->references('id')->on('plans');
            $table->string('kriteria_id')->references('id')->on('kriterias')->nullable();
            $table->float('nilai_matriks_ternormalisasi')->nullable();
            $table->float('nilai_ternormalisasi_terbobot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perhitungans');
    }
};

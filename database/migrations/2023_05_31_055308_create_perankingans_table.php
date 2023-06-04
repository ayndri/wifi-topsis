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
        Schema::create('perankingans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_id')->references('id')->on('plans');
            $table->float('nilai_solusi_positif')->nullable();
            $table->float('nilai_solusi_negatif')->nullable();
            $table->float('preferensi')->nullable();
            $table->float('perangkingan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perankingans');
    }
};

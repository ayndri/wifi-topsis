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
        Schema::create('detail_kriterias', function (Blueprint $table) {
            $table->id();
            $table->string('kriteria_id')->references('id')->on('kriterias');
            $table->integer('poin');
            $table->string('keterangan');
            $table->string('data_optional');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_kriterias');
    }
};

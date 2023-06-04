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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('paket_id')->references('id')->on('paket_data');
            $table->float('kecepatan', 20, 2)->nullable();
            $table->float('jumlah_perangkat')->nullable();
            $table->float('jenis_ip')->nullable();
            $table->float('jenis_layanan')->nullable();
            $table->float('rekomendasi_perangkat')->nullable();
            $table->float('rasio_down_up');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

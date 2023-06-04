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
        Schema::create('pembagis', function (Blueprint $table) {
            $table->id();
            $table->string('kriteria_id')->references('id')->on('kriterias');
            $table->float('nilai', 20, 2)->nullable();
            $table->float('nilai_max')->nullable();
            $table->float('nilai_min')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembagis');
    }
};

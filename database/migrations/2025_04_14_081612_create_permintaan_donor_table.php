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
        Schema::create('permintaan_donor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerima_id')->constrained()->onDelete('cascade');
            $table->foreignId('pendonor_id')->constrained()->onDelete('cascade');
            $table->boolean('dikonfirmasi')->default(false); // bisa pakai notifikasi manual atau model
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_donor');
    }
};

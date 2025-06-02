<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_telp');
            $table->string('golongan_darah');
            $table->string('asal_daerah');
            $table->text('riwayat_transfusi')->nullable(); // opsional
            $table->timestamps();
        });
    }
    
};

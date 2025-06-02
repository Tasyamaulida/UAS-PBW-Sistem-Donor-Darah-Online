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
        Schema::create('donor_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // user penerima permintaan donor
            $table->string('blood_type');
            $table->string('location');
            $table->text('message')->nullable();
            $table->timestamps();

            // foreign key ke users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_requests');
    }
};

<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_pendonor_id_and_status_to_donor_requests_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('donor_requests', function (Blueprint $table) {
            $table->foreignId('pendonor_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending')->after('message'); // Nilai default 'pending'
        });
    }

    public function down()
    {
        Schema::table('donor_requests', function (Blueprint $table) {
            $table->dropForeign(['pendonor_id']);
            $table->dropColumn('pendonor_id');
            $table->dropColumn('status');
        });
    }
};
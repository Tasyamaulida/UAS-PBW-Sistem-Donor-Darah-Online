<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Tambahkan setelah kolom to_user_id atau di mana pun yang sesuai
            $table->foreignId('donor_request_id')->nullable()->after('to_user_id')->constrained('donor_requests')->onDelete('set null');
            // onDelete('set null') berarti jika DonorRequest dihapus, donor_request_id di pesan akan jadi NULL
            // Anda bisa juga menggunakan onDelete('cascade') jika ingin pesan terkait ikut terhapus
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['donor_request_id']);
            $table->dropColumn('donor_request_id');
        });
    }
};
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',        // ID User pembuat permintaan (Penerima)
        'pendonor_id',    // ID User Pendonor yang menerima/merespon (nullable)
        'blood_type',
        'location',
        'message',
        'status',         // Kolom status: 'pending', 'diterima', 'ditolak', 'selesai'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke user pembuat permintaan (Penerima).
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke user pendonor yang merespon/menerima permintaan.
     */
    public function donor()
    {
        return $this->belongsTo(User::class, 'pendonor_id');
    }

    /**
     * Relasi ke pesan-pesan chat terkait permintaan ini (jika ada).
     * Asumsi tabel 'messages' memiliki kolom 'donor_request_id'.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'donor_request_id');
    }
}
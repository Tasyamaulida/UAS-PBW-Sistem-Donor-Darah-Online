<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanDonor extends Model
{
    use HasFactory;

    protected $table = 'permintaan_donor'; // Sesuaikan nama tabel di database kamu
    protected $fillable = ['penerima_id', 'pendonor_id', /* kolom lainnya */];  
    public function pendonor()
{
    return $this->belongsTo(Pendonor::class);
}


    public function penerima()
    {
        return $this->belongsTo(Penerima::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penerima extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'no_telp',
        'golongan_darah',
        'asal_daerah',
        'riwayat_transfusi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

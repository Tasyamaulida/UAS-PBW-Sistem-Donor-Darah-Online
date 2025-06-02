<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pendonor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PendonorSeeder extends Seeder
{
    public function run(): void
    {
        $baseBloodTypes = ['A', 'B', 'AB', 'O']; // Tipe darah dasar
        $rhFactors = ['+', '-']; // Faktor Rh
        $locations = ['Jakarta', 'Bandung', 'Surabaya', 'Banda Aceh', 'Medan'];

        // Membuat 10 data dummy pendonor
        for ($i = 1; $i <= 10; $i++) {
            // Membuat user
            // Anda bisa memilih untuk menyimpan blood_type user dengan atau tanpa Rh.
            // Di sini, saya biarkan User.blood_type tanpa Rh, tapi Pendonor.golongan_darah akan memiliki Rh.
            $user = User::create([
                'name' => 'Pendonor ' . $i,
                'email' => 'donor' . $i . '@example.com',
                'password' => Hash::make('password123'), // Default password
                'phone' => '08' . substr(str_shuffle("0123456789"), 0, 10), // Cara lain generate nomor acak
                'blood_type' => $baseBloodTypes[array_rand($baseBloodTypes)], // User's blood_type bisa tetap A, B, dll.
                'location' => $locations[array_rand($locations)],
                'role' => 'pendonor',
            ]);

            // Menentukan golongan darah lengkap (dengan Rh) untuk Pendonor
            $pendonorBaseBloodType = $baseBloodTypes[array_rand($baseBloodTypes)];
            $pendonorRhFactor = $rhFactors[array_rand($rhFactors)];
            $pendonorFullBloodType = $pendonorBaseBloodType . $pendonorRhFactor;

            // Membuat data pendonor terkait user
            Pendonor::create([
                'user_id' => $user->id,
                'nama' => $user->name, // Menggunakan 'nama'
                'no_telp' => $user->phone, // Menggunakan 'no_telp'
                'golongan_darah' => $pendonorFullBloodType, // SEKARANG DENGAN +/-
                'asal_daerah' => $user->location,
                'riwayat_donor' => rand(0, 5) . 'x donor sejak 202' . rand(0, 3), // Rand(0,5) agar bisa 0x
                'status' => 'tersedia',
            ]);
        }
    }
}
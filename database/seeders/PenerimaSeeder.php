<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Penerima;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PenerimaSeeder extends Seeder
{
    public function run(): void
    {
        $bloodTypes = ['A', 'B', 'AB', 'O'];
        $locations = ['Jakarta', 'Bandung', 'Surabaya', 'Banda Aceh', 'Medan'];

        for ($i = 1; $i <= 10; $i++) {
            // Buat user dulu
            $user = User::create([
                'name' => 'Penerima ' . $i,
                'email' => 'penerima' . $i . '@example.com',
                'password' => Hash::make('password123'),
                'phone' => '08' . rand(1111111111, 9999999999),
                'blood_type' => $bloodTypes[array_rand($bloodTypes)],
                'location' => $locations[array_rand($locations)],
                'role' => 'penerima',
            ]);

            // Buat data penerima yang terhubung ke user
            Penerima::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'no_telp' => $user->phone,
                'golongan_darah' => $user->blood_type,
                'asal_daerah' => $user->location,
                'status' => 'menunggu',
                'kebutuhan' => rand(1, 3) . ' kantong darah',
            ]);
        }
    }
}

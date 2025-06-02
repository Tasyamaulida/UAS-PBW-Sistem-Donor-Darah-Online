@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white rounded-lg shadow-lg p-6">
        <!-- Kartu Profil Pendonor -->
        <div class="border-r border-gray-200 pr-6">
            <div class="flex flex-col items-center">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($pendonor->nama) }}" alt="Avatar" class="w-24 h-24 rounded-full mb-4">
                <h2 class="text-2xl font-bold">{{ $pendonor->nama }}</h2>
                <p class="mt-4"><strong>Golongan Darah:</strong> {{ $pendonor->golongan_darah }}</p>
                <p><strong>Lokasi:</strong> {{ $pendonor->asal_daerah }}</p>
                <p class="mt-4 text-sm text-gray-600">
                    Riwayat Donor: {{ $pendonor->riwayat_donor ?? 'Belum ada data' }}
                </p>
                <a href="#chat" class="mt-4 inline-block bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Hubungi</a>
            </div>
        </div>

        <!-- Chat Box -->
        <div id="chat">
            <h2 class="text-2xl font-bold mb-4">{{ $pendonor->nama }}</h2>
            <div class="bg-gray-100 p-4 rounded mb-4">
                <p>Halo, saya membutuhkan darah {{ $pendonor->golongan_darah }} di daerah {{ $pendonor->asal_daerah }}. Apakah Anda bersedia membantu?</p>
            </div>

            <!-- Pesan sukses -->
            @if (session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('pendonor.kirimPesan', $pendonor->id) }}" method="POST" class="flex items-center space-x-2">
                @csrf
                <input type="text" name="pesan" placeholder="Tulis pesan..." class="flex-1 border border-gray-300 rounded px-3 py-2 focus:outline-none" required>
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Kirim</button>
            </form>
        </div>
    </div>
</div>
@endsection

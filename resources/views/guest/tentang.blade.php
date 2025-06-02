@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card border-0 shadow-sm p-4">
            <h2 class="text-center text-danger mb-4">Tentang Sistem Donor Darah Online</h2>
            <p>
                Website ini dibuat sebagai solusi atas permasalahan kurangnya akses cepat dan mudah untuk mendapatkan donor darah yang sesuai. 
                Kami percaya bahwa teknologi bisa menjadi jembatan untuk menyelamatkan nyawa.
            </p>
            <p>
                Melalui sistem ini, pengguna bisa:
            </p>
            <ul>
                <li>Mendaftar sebagai pendonor atau penerima darah</li>
                <li>Mencari pendonor berdasarkan golongan darah dan lokasi</li>
                <li>Mengirim dan menerima notifikasi permintaan darah secara real-time</li>
                <li>Melihat riwayat aktivitas donor</li>
                <li>Melihat edukasi mengenai manfaat donor darah</li>
            </ul>
            <p>
                Sistem ini ditujukan untuk rumah sakit, instansi kesehatan, dan masyarakat umum yang ingin turut serta menyelamatkan nyawa melalui donor darah.
            </p>
            <div class="text-center mt-4">
                <img src="{{ asset('images/blood.png') }}" width="100" alt="Blood Icon">
                <p class="mt-2 text-muted"><em>Setetes darahmu adalah awal dari harapan — satu platform untuk mengalirkan hidup, hari ini dan esok hari.</em></p>
            </div>
        </div>
    </div>
</div>
@endsection

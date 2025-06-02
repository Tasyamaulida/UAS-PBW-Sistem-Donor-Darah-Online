@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container py-5 text-center">

    {{-- Pesan sambutan personal --}}
    <h2 class="text-danger fw-bold mb-2">Halo, {{ $user_name ?? 'Pengguna' }}!</h2>
    <p class="mb-4 text-muted">Selamat datang di platform Donor Darah. Silakan pilih peran Anda untuk melanjutkan ke halaman yang sesuai.</p>

    {{-- Gambar kiri dan kanan --}}
    <div class="row justify-content-center align-items-center mb-4">
        <div class="col-md-2 d-none d-md-block">
            <img src="{{ asset('images/person-left.png') }}" alt="Gambar Kiri" class="img-fluid">
        </div>
        <div class="col-md-8">
            {{-- Pilihan peran --}}
            <div class="row justify-content-center">
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <i class="bi bi-heart-pulse-fill text-danger fs-1 mb-3"></i>
                            <h5 class="card-title">Sebagai Pendonor</h5>
                            <p class="text-muted">Akses data donor, jadwal, dan riwayat donasi Anda.</p>
                            <a href="{{ route('pendonor.create') }}" class="btn btn-red mt-2">Masuk</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <i class="bi bi-droplet-half text-danger fs-1 mb-3"></i>
                            <h5 class="card-title">Sebagai Penerima</h5>
                            <p class="text-muted">Lihat ketersediaan darah dan ajukan permintaan.</p>
                            <a href="{{ route('dashboard.penerima') }}" class="btn btn-outline-danger mt-2">Masuk</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 d-none d-md-block">
            <img src="{{ asset('images/person-right.png') }}" alt="Gambar Kanan" class="img-fluid">
        </div>
    </div>

    
</div>
@endsection

@push('styles')
<!-- Tambahkan Bootstrap Icons jika belum ada -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endpush

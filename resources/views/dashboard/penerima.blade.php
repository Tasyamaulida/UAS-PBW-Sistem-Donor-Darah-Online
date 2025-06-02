@extends('layouts.app')

@section('title', 'Dashboard Penerima') {{-- Judul dari kode Anda sebelumnya --}}

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-danger fw-bold">Daftar Penerima Darah</h2> {{-- Judul utama dari kode Anda --}}

    {{-- ✅ Alert Messages (Tidak Diubah) --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- Tambahkan alert lain jika perlu dan sudah ada --}}

    {{-- ✅ Tombol Aksi (Tidak Diubah) --}}
    <div class="mb-4">
        <a href="{{ route('penerima.create') }}" class="btn btn-danger me-2">+ Tambah Penerima</a>
        <a href="{{ route('penerimas.cariPendonor') }}" class="btn btn-outline-danger">Cari Pendonor 🔍</a>
    </div>

    {{-- ✅ Notifikasi Status Permintaan Donor (BAGIAN YANG DIPERBAIKI) --}}
    <div class="mb-5">
        <h5 class="fw-bold">Status Permintaan Donor Anda</h5>

        @php
            // Ambil notifikasi yang BELUM DIBACA untuk user ini
            // yang tipenya adalah PermintaanDonorDiterima atau PermintaanDonorDitolak
            // (Pastikan nama kelas notifikasi ini benar sesuai implementasi Anda)
            $permintaanStatusNotifications = Auth::user()->unreadNotifications->filter(function ($notification) {
                return in_array($notification->type, [
                    'App\\Notifications\\PermintaanDonorDiterima', // Path lengkap ke kelas Notifikasi
                    'App\\Notifications\\PermintaanDonorDitolak',   // Path lengkap ke kelas Notifikasi
                    // Jika ada notifikasi "Selesai":
                    // 'App\\Notifications\\PermintaanDonorSelesaiNotification'
                ]) && isset($notification->data['permintaan_id']); // Pastikan ada ID permintaan di data notifikasi
            });
        @endphp

        @if($permintaanStatusNotifications->count() > 0)
            <ul class="list-group shadow-sm">
                @foreach($permintaanStatusNotifications as $notification)
                    @php
                        // Ambil data dari notifikasi dengan aman
                        $statusPermintaan = $notification->data['status'] ?? null; // 'diterima', 'ditolak', 'selesai'
                        $pesanNotifikasi = $notification->data['pesan'] ?? 'Anda memiliki update permintaan donor.';
                        $permintaanId = $notification->data['permintaan_id'] ?? null;
                        $urlChat = $notification->data['url_chat'] ?? null; // Untuk notifikasi diterima

                        // Ambil detail DonorRequest untuk informasi tambahan (opsional tapi baik)
                        $donorRequest = $permintaanId ? \App\Models\DonorRequest::find($permintaanId) : null;
                    @endphp

                    {{-- Hanya tampilkan jika statusnya valid (diterima atau ditolak atau selesai) --}}
                    @if(in_array($statusPermintaan, ['diterima', 'ditolak', 'selesai']))
                        <li class="list-group-item d-flex justify-content-between align-items-center
                            @if($statusPermintaan == 'diterima') list-group-item-success @php $badgeClass = 'bg-light text-success border border-success'; @endphp
                            @elseif($statusPermintaan == 'ditolak') list-group-item-danger @php $badgeClass = 'bg-light text-danger border border-danger'; @endphp
                            @elseif($statusPermintaan == 'selesai') list-group-item-info @php $badgeClass = 'bg-light text-info border border-info'; @endphp
                            @else list-group-item-light @php $badgeClass = 'bg-secondary'; @endphp @endif">

                            <div>
                                <p class="mb-1 fw-semibold">{{ $pesanNotifikasi }}</p>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                    @if($donorRequest)
                                        (Permintaan #{{ $donorRequest->id }} untuk Gol. {{ $donorRequest->blood_type }})
                                    @endif
                                </small>
                            </div>

                            <div class="ms-2 text-nowrap"> {{-- text-nowrap agar tombol tidak turun --}}
                                @if($statusPermintaan == 'diterima' && $urlChat)
                                    <a href="{{ $urlChat }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-comments"></i> Chat
                                    </a>
                                    {{-- Tombol Selesai biasanya tidak ada di sisi penerima untuk aksi langsung --}}
                                    {{-- Jika pendonor menandai selesai, status akan berubah jadi 'selesai' --}}

                                @elseif($statusPermintaan == 'ditolak')
                                    <span class="badge {{ $badgeClass }} p-2">Permintaan Ditolak</span>
                                @elseif($statusPermintaan == 'selesai')
                                    <span class="badge {{ $badgeClass }} p-2">Proses Selesai</span>
                                @endif
                                {{-- Opsional: Tombol untuk menandai notifikasi sudah dibaca --}}
                                {{--
                                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline ms-1">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm p-1" title="Tandai sudah dibaca">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                --}}
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        @else
            <p class="text-muted">Belum ada update status permintaan donor terbaru untuk Anda.</p>
        @endif
    </div>

    {{-- ✅ Tabel Data Penerima (Tidak Diubah dari kode Anda) --}}
    @if ($penerimas->isEmpty())
        <p class="text-muted">Belum ada data penerima. Silakan tambahkan terlebih dahulu.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-danger">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>No. Telepon</th>
                        <th>Gol. Darah Dibutuhkan</th>
                        <th>Asal Daerah</th>
                        <th>Riwayat Transfusi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penerimas as $index => $penerima)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $penerima->nama }}</td>
                            <td>{{ $penerima->no_telp }}</td>
                            <td>{{ $penerima->golongan_darah }}</td>
                            <td>{{ $penerima->asal_daerah }}</td>
                            <td>{{ $penerima->riwayat_transfusi ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
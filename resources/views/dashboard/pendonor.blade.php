@extends('layouts.app')

@section('title', 'Data Pendonor')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-danger fw-bold">Daftar Pendonor Anda</h2>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($pendonors->isNotEmpty()) {{-- $pendonors dari controller --}}
        <div class="mb-4">
            {{-- Form update status menggunakan $pendonors[0] karena Anda menggunakan collection --}}
            {{-- Jika Anda beralih ke $profilPendonor (singular) di controller, sesuaikan ini --}}
            <form action="{{ route('pendonor.updateStatus', $pendonors[0]->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <label for="status" class="form-label">Status Ketersediaan Anda:</label>
                <select name="status" id="status" class="form-select w-auto d-inline mx-2">
                    <option value="Tersedia" {{ $pendonors[0]->status == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="Tidak Tersedia" {{ $pendonors[0]->status == 'Tidak Tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
            </form>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
     @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
     @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <a href="{{ route('pendonor.create') }}" class="btn btn-danger mb-4">+ Tambah Pendonor</a>

    {{-- ✅ Notifikasi Permintaan Donor BARU (Kode Anda yang sudah ada dan berfungsi) --}}
    <div class="mb-5">
        <h5 class="fw-bold">Notifikasi Permintaan Donor Baru</h5>
        @php
            // Logika filter notifikasi Anda sudah benar di sini
            $unreadRelevantNotifications = Auth::user()->unreadNotifications->filter(function ($notification) {
                return $notification->type === 'App\\Notifications\\PermintaanBaruNotification' &&
                       isset($notification->data['permintaan_id']) &&
                       isset($notification->data['pesan']);
            });
        @endphp

        @if ($unreadRelevantNotifications->count() > 0)
            <ul class="list-group shadow-sm">
                @foreach ($unreadRelevantNotifications as $notification)
                    @php
                        $donorRequest = null;
                        if (isset($notification->data['permintaan_id'])) {
                            // Pastikan DonorRequest diambil untuk logika tombol
                            $donorRequest = \App\Models\DonorRequest::find($notification->data['permintaan_id']);
                        }
                    @endphp
                    {{-- Tampilkan item notifikasi hanya jika $donorRequest ditemukan dan statusnya pending --}}
                    {{-- Atau sesuaikan jika Anda ingin notifikasi tetap ada tapi tombol disable --}}
                    @if ($donorRequest && $donorRequest->status === 'pending')
                        <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div class="mb-2 mb-md-0">
                                <p class="mb-0 fw-semibold">
                                    {{ $notification->data['pesan'] ?? 'Ada permintaan donor baru' }}
                                    (Permintaan #{{ $donorRequest->id }}) {{-- Tampilkan ID DonorRequest --}}
                                </p>
                                <small class="text-muted">
                                    Diterima: {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                </small>
                            </div>
                            <div class="ms-md-auto mt-2 mt-md-0">
                                <form action="{{ route('donor.requests.accept', ['id' => $donorRequest->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success me-1">Terima</button>
                                </form>
                                <form action="{{ route('donor.requests.decline', ['id' => $donorRequest->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                                </form>
                            </div>
                        </li>
                    @elseif($donorRequest)
                         <li class="list-group-item list-group-item-light d-flex justify-content-between align-items-center">
                             <div>
                                <p class="mb-0 text-muted">
                                    {{ $notification->data['pesan'] ?? 'Ada permintaan donor baru' }}
                                     (Permintaan #{{ $donorRequest->id }})
                                </p>
                                <small class="text-muted">
                                    Diterima: {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                </small>
                            </div>
                            <span class="badge bg-info text-dark">Permintaan Sudah Direspon (Status: {{ ucfirst($donorRequest->status) }})</span>
                        </li>
                    @else
                         <li class="list-group-item list-group-item-warning d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-0 text-muted">{{ $notification->data['pesan'] ?? 'Notifikasi permintaan donor.' }}</p>
                                <small class="text-muted">Diterima: {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small>
                            </div>
                            <span class="badge bg-warning text-dark">Detail permintaan tidak ditemukan. (ID: {{ $notification->data['permintaan_id'] ?? 'N/A' }})</span>
                        </li>
                    @endif
                @endforeach
            </ul>
        @else
            <p class="text-muted">Belum ada notifikasi permintaan donor baru.</p>
        @endif
    </div>

    {{-- =============== BAGIAN BARU: PERMINTAAN YANG DITANGANI =============== --}}
    <div class="mt-5 mb-5">
        <h5 class="fw-bold">Permintaan Donor yang Anda Tangani</h5>
        @if(isset($permintaanDitangani) && $permintaanDitangani->count() > 0)
            <div class="list-group shadow-sm">
                @foreach($permintaanDitangani as $requestDitangani)
                    <div class="list-group-item list-group-item-action flex-column align-items-start
                        @if($requestDitangani->status == 'diterima') list-group-item-primary-soft @endif
                        @if($requestDitangani->status == 'selesai') list-group-item-secondary-soft @endif">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">
                                Permintaan dari: {{ $requestDitangani->requester->name ?? 'Penerima Tidak Dikenal' }}
                                (Gol. {{ $requestDitangani->blood_type }})
                            </h6>
                            <small class="text-muted">{{ $requestDitangani->updated_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">
                            Status: <span class="fw-bold">{{ ucfirst($requestDitangani->status) }}</span>
                            (ID Permintaan: #{{ $requestDitangani->id }})
                        </p>
                        @if($requestDitangani->message)
                        <small class="text-muted d-block">Pesan Pemohon: {{ Str::limit($requestDitangani->message, 70) }}</small>
                        @endif

                        <div class="mt-2 text-end">
                            {{-- Tombol Chat jika status 'diterima' atau 'selesai' --}}
                            @if(in_array($requestDitangani->status, ['diterima', 'selesai']))
                                <a href="{{ route('chat.show', [
                                        'user' => $requestDitangani->requester->id, // Lawan bicara adalah penerima
                                        'request_id' => $requestDitangani->id,       // Konteks DonorRequest
                                        'redirect_url' => route('dashboard.pendonor') // Untuk tombol kembali di chat
                                    ]) }}" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-comments"></i> {{ $requestDitangani->status == 'diterima' ? 'Lanjutkan Chat' : 'Lihat Riwayat Chat' }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted">Anda belum menangani permintaan donor yang diterima atau sudah selesai.</p>
        @endif
    </div>
    {{-- =============== SELESAI BAGIAN BARU =============== --}}


    {{-- ✅ Tabel Data Profil Pendonor Anda (Kode Anda yang sudah ada) --}}
    {{-- Menggunakan $pendonors (plural) sesuai kode asli Anda --}}
    @if (!$pendonors->isEmpty()) {{-- Perbaikan: Cek apakah $pendonors tidak kosong, bukan $profilPendonor --}}
        <h5 class="fw-bold mt-4">Profil Pendonor Anda</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-danger">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>No. Telepon</th>
                        <th>Gol. Darah</th>
                        <th>Asal Daerah</th>
                        <th>Riwayat Donor</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendonors as $index => $pendonor)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $pendonor->nama }}</td>
                            <td>{{ $pendonor->no_telp }}</td>
                            <td>{{ $pendonor->golongan_darah }}</td>
                            <td>{{ $pendonor->asal_daerah }}</td>
                            <td>{{ $pendonor->riwayat_donor ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $pendonor->status == 'Tersedia' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $pendonor->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('pendonor.edit', ['id' => $pendonor->id]) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('pendonor.destroy', ['id' => $pendonor->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted mt-3">Data profil pendonor Anda belum ada. Silakan tambahkan melalui tombol "+ Tambah Pendonor".</p>
    @endif
</div>
@endsection

@push('styles')
<style>
    .list-group-item-primary-soft { /* Contoh styling untuk permintaan diterima */
        color: #084298;
        background-color: #cfe2ff;
        border-color: #b6d4fe;
    }
    .list-group-item-secondary-soft { /* Contoh styling untuk permintaan selesai */
        color: #41464b;
        background-color: #e2e3e5;
        border-color: #d3d6d8;
    }
</style>
@endpush
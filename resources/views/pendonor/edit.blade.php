@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('title', 'Edit Data Pendonor')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold" style="color: #FFA500;">Edit Data Pendonor</h2>
        </div>
    </div>

    <form action="{{ route('pendonor.update', $pendonor->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow-sm">
            <div class="card-body p-4"> {{-- Tambah padding --}}
                {{-- Nama Lengkap (menggunakan field 'nama') --}}
                <div class="mb-3">
                    <label for="nama" class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama', $pendonor->nama) }}" class="form-control @error('nama') is-invalid @enderror" required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nomor Telepon (menggunakan field 'no_telp') --}}
                <div class="mb-3">
                    <label for="no_telp" class="form-label fw-semibold">Nomor Telepon</label>
                    <input type="text" name="no_telp" id="no_telp" value="{{ old('no_telp', $pendonor->no_telp) }}" class="form-control @error('no_telp') is-invalid @enderror" required>
                    @error('no_telp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Golongan Darah --}}
                <div class="mb-3">
                    <label for="golongan_darah_select" class="form-label fw-semibold">Golongan Darah</label>
                    <select name="golongan_darah" id="golongan_darah_select" class="form-select @error('golongan_darah') is-invalid @enderror" required>
                        {{-- <option value="" disabled>-- Pilih Golongan Darah --</option> --}}
                        @php
                            $bloodOptions = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                            $selectedBloodValue = old('golongan_darah', $pendonor->golongan_darah);
                        @endphp
                        @foreach ($bloodOptions as $option)
                            <option value="{{ $option }}" {{ $selectedBloodValue == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    @error('golongan_darah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Asal Daerah --}}
                <div class="mb-3">
                    <label for="asal_daerah" class="form-label fw-semibold">Asal Daerah</label>
                    <input type="text" name="asal_daerah" id="asal_daerah" value="{{ old('asal_daerah', $pendonor->asal_daerah) }}" class="form-control @error('asal_daerah') is-invalid @enderror" required>
                    @error('asal_daerah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Riwayat Donor/Transfusi (opsional) --}}
                <div class="mb-3">
                    <label for="riwayat_donor" class="form-label fw-semibold">Riwayat Donor/Transfusi (opsional)</label>
                    <textarea name="riwayat_donor" id="riwayat_donor" class="form-control @error('riwayat_donor') is-invalid @enderror" rows="3">{{ old('riwayat_donor', $pendonor->riwayat_donor) }}</textarea>
                    @error('riwayat_donor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary me-2">Update Data</button>
                    <a href="{{ route('dashboard.pendonor') }}" class="btn btn-secondary">Batal</a>
                </div>

            </div> {{-- end card-body --}}
        </div> {{-- end card --}}
    </form>
</div>
@endsection
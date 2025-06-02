@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Judul disesuaikan dengan screenshot --}}
    <h2 style="color: orange; font-weight: bold;">Edit Data Pendonor</h2>

    {{-- Pastikan action route dan variabel objeknya benar untuk pendonor --}}
    <form action="{{ route('pendonor.update', $pendonor->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nama_lengkap">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $pendonor->nama_lengkap) }}" class="form-control @error('nama_lengkap') is-invalid @enderror">
            @error('nama_lengkap')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="nomor_telepon">Nomor Telepon</label>
            <input type="text" name="nomor_telepon" id="nomor_telepon" value="{{ old('nomor_telepon', $pendonor->nomor_telepon) }}" class="form-control @error('nomor_telepon') is-invalid @enderror">
            @error('nomor_telepon')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            {{-- PERBAIKAN UTAMA ADA DI LABEL INI --}}
            <label for="golongan_darah_select">Golongan Darah</label>
            <select name="golongan_darah" id="golongan_darah_select" class="form-control @error('golongan_darah') is-invalid @enderror">
                {{-- <option value="">-- Pilih Golongan Darah --</option> --}}

                @php
                    $options = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                    // Ganti $pendonor dengan variabel objek pendonor Anda
                    // Pastikan $pendonor->golongan_darah ada dan berisi nilai yang benar
                    $selectedValue = old('golongan_darah', $pendonor->golongan_darah ?? '');
                @endphp

                @foreach ($options as $option)
                    <option value="{{ $option }}" {{ $selectedValue == $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
            @error('golongan_darah')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="asal_daerah">Asal Daerah</label>
            <input type="text" name="asal_daerah" id="asal_daerah" value="{{ old('asal_daerah', $pendonor->asal_daerah) }}" class="form-control @error('asal_daerah') is-invalid @enderror">
            @error('asal_daerah')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="riwayat_donor">Riwayat Donor/Transfusi (opsional)</label>
            <textarea name="riwayat_donor" id="riwayat_donor" class="form-control @error('riwayat_donor') is-invalid @enderror">{{ old('riwayat_donor', $pendonor->riwayat_donor ?? '') }}</textarea>
            @error('riwayat_donor')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        {{-- Sesuaikan route untuk tombol batal jika perlu --}}
        {{-- <a href="{{ route('pendonor.index') }}" class="btn btn-secondary">Batal</a> --}}
    </form>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Form Pendonor')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-danger fw-bold">Lengkapi Data Pendonor</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Oops!</strong> Ada masalah dengan data yang kamu isi.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pendonor.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
        </div>

        <div class="mb-3">
            <label for="no_telp" class="form-label">Nomor Telepon</label>
            <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp') }}" required>
        </div>


        <div class="mb-3">
            <label for="golongan_darah" class="form-label">Golongan Darah</label>
            <select name="golongan_darah" class="form-select" required>
                <option value="">-- Pilih Golongan Darah --</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>
        </div>


        <div class="mb-3">
            <label for="asal_daerah" class="form-label">Asal Daerah</label>
            <input type="text" name="asal_daerah" class="form-control" value="{{ old('asal_daerah') }}" required>
        </div>

        <div class="mb-3">
            <label for="riwayat_donor" class="form-label">Riwayat Donor (opsional)</label>
            <textarea name="riwayat_donor" class="form-control">{{ old('riwayat_donor') }}</textarea>
        </div>

        <button type="submit" class="btn btn-danger">Simpan Data</button>
    </form>
</div>
@endsection

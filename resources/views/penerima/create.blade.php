@extends('layouts.app')

@section('title', 'Form Data Penerima')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-danger fw-bold">Formulir Data Penerima</h2>
    <form action="{{ route('penerima.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="nama" name="nama" required>
        </div>

        <div class="mb-3">
            <label for="no_telp" class="form-label">Nomor Telepon</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" required>
        </div>

        <div class="mb-3">
            <label for="golongan_darah" class="form-label">Golongan Darah yang Dibutuhkan</label>
            <select class="form-select" id="golongan_darah" name="golongan_darah" required>
                <option value="" disabled selected>Pilih Golongan Darah</option>
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
            <input type="text" class="form-control" id="asal_daerah" name="asal_daerah" required>
        </div>

        <div class="mb-3">
            <label for="riwayat_transfusi" class="form-label">Riwayat Transfusi (opsional)</label>
            <textarea class="form-control" id="riwayat_transfusi" name="riwayat_transfusi" rows="3"></textarea>
        </div>


        <button type="submit" class="btn btn-danger">Simpan Data</button>
    </form>
</div>
@endsection

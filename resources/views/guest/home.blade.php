@extends('layouts.app')

@section('content')
    <div class="text-center my-5">
        <h2>Sistem Donor Darah Online Terintegrasi</h2>

        <form action="{{ route('penerimas.cariPendonor') }}" method="GET" class="d-flex justify-content-center gap-2 mt-4">
            <select class="form-select w-auto" name="golongan">
                <option value="">Golongan Darah</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="AB">AB</option>
                <option value="O">O</option>
            </select>
            <select class="form-select w-auto" name="kota">
                <option value="">Kota</option>
                <option value="Jakarta">Jakarta</option>
                <option value="Bandung">Bandung</option>
                <option value="Surabaya">Surabaya</option>
            </select>
            <button type="submit" class="btn btn-red">Cari Pendonor</button>
        </form>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-3">
            <div class="card card-red mb-3 text-center">
                <div class="card-body">
                    <h5 class="card-title">John Doe</h5>
                    <h3 class="text-danger">A</h3>
                    <p>Jakarta</p>
                    <a href="{{ route('penerimas.cariPendonor') }}" class="btn btn-red">Minta Darah</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-red mb-3 text-center">
                <div class="card-body">
                    <h5 class="card-title">Jane Smith</h5>
                    <h3 class="text-danger">B</h3>
                    <p>Surabaya</p>
                    <a href="{{ route('penerimas.cariPendonor') }}" class="btn btn-red">Minta Darah</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-red mb-3 text-center">
                <div class="card-body">
                    <h5 class="card-title">Robert Johnson</h5>
                    <h3 class="text-danger">O</h3>
                    <p>Bandung</p>
                    <a href="{{ route('penerimas.cariPendonor') }}" class="btn btn-red">Minta Darah</a>
                </div>
            </div>
        </div>
    </div>
@endsection

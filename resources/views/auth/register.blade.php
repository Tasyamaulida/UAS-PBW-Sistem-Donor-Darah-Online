@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="text-center text-danger mb-4">Register</h2>

        {{-- Notifikasi sukses --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text" name="name" class="form-control" id="name" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" id="email" placeholder="Masukkan email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" name="password" class="form-control" id="password" placeholder="Masukkan kata sandi" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" class="form-control" id="confirm_password" placeholder="Ulangi kata sandi" required>
            </div>
            <button type="submit" class="btn btn-red w-100">Register</button>
        </form>

        <div class="text-center mt-3">
            <span>Sudah punya akun? <a href="{{ route('login') }}" class="text-danger text-decoration-none">Login di sini</a></span>
        </div>
    </div>
</div>
@endsection

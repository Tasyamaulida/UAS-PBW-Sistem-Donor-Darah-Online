@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="text-center text-danger mb-4">Login</h2>

        {{-- Notifikasi sukses atau error --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Notifikasi error dari validasi --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    class="form-control @error('email') is-invalid @enderror" 
                    name="email" 
                    id="email" 
                    placeholder="Masukkan email" 
                    required 
                    value="{{ old('email') }}" 
                    autofocus>
                @error('email')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input 
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    name="password" 
                    id="password" 
                    placeholder="Masukkan kata sandi" 
                    required>
                @error('password')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror

                <div class="text-end mt-1">
                    <a href="#" class="text-decoration-none text-danger" style="font-size: 0.9rem;">Lupa kata sandi?</a>
                </div>
            </div>

            <button type="submit" class="btn btn-red w-100">Login</button>
        </form>

        {{-- Link ke halaman register --}}
        <div class="text-center mt-3">
            <span>Belum punya akun? <a href="{{ route('register') }}" class="text-danger text-decoration-none">Daftar sekarang</a></span>
        </div>
    </div>
</div>
@endsection

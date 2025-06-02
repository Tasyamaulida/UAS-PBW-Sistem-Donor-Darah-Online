@extends('layouts.app')

@section('title', 'Detail Permintaan Donor')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-danger fw-bold">Detail Permintaan Donor</h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Golongan Darah:</strong> {{ $request->blood_type }}</p>
            <p><strong>Daerah:</strong> {{ $request->location }}</p>
            <p><strong>Pesan:</strong> {{ $request->message ?? '-' }}</p>
            <p><strong>Pengirim Permintaan:</strong> {{ $request->user->name }}</p>
        </div>
    </div>

    @if($request->status !== 'Diterima')
        <div class="d-flex gap-2 mb-3">
            {{-- Tombol Terima --}}
            <form action="{{ route('donor.requests.accept', $request->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Terima Permintaan</button>
            </form>

            {{-- Tombol Tolak --}}
            <form action="{{ route('donor.requests.decline', $request->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger">Tolak Permintaan</button>
            </form>
        </div>
    @else
        <div class="alert alert-info">Permintaan ini telah diterima. Silakan lanjutkan ke halaman chat untuk berkomunikasi.</div>
        <a href="{{ route('chat.show', $request->user_id) }}" class="btn btn-primary">Buka Chat</a>
    @endif

    <a href="{{ route('dashboard.pendonor') }}" class="btn btn-secondary mt-3">Kembali ke Dashboard</a>
</div>
@endsection

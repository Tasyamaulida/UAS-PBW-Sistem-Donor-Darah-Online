@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="text-danger fw-bold mb-4">Manfaat Donor Darah bagi Kesehatan</h2>

    <div class="row">
        <div class="col-md-8">
            {{-- 1 --}}
            <div class="d-flex align-items-start mb-4">
                <img src="{{ asset('images/heart-icon.png') }}" alt="Ikon Jantung" class="me-3" style="width: 40px; height: 40px;">
                <div>
                    <h5 class="fw-bold">1. Menjaga Kesehatan Jantung</h5>
                    <p style="text-align: justify;">
                        Donor darah secara rutin membantu menurunkan kadar zat besi berlebih dalam tubuh yang bisa mengurangi risiko penyakit jantung dan pembuluh darah.
                    </p>
                </div>
            </div>

            {{-- 2 --}}
            <div class="d-flex align-items-start mb-4">
                <img src="{{ asset('images/blood-flow-icon.png') }}" alt="Ikon Aliran Darah" class="me-3" style="width: 40px; height: 40px;">
                <div>
                    <h5 class="fw-bold">2. Melancarkan Aliran Darah</h5>
                    <p style="text-align: justify;">
                        Dengan mendonorkan darah, sirkulasi darah menjadi lebih lancar dan membantu mencegah pengentalan darah yang dapat menyebabkan penyumbatan.
                    </p>
                </div>
            </div>

            {{-- 3 --}}
            <div class="d-flex align-items-start mb-4">
                <img src="{{ asset('images/blood-cell-icon.png') }}" alt="Ikon Sel Darah" class="me-3" style="width: 40px; height: 40px;">
                <div>
                    <h5 class="fw-bold">3. Meningkatkan Produksi Sel Darah</h5>
                    <p style="text-align: justify;">
                        Setelah darah didonorkan, tubuh secara alami akan memproduksi sel darah merah baru, yang bermanfaat bagi regenerasi dan kesehatan tubuh secara keseluruhan.
                    </p>
                </div>
            </div>

            {{-- 4 --}}
            <div class="d-flex align-items-start mb-4">
                <img src="{{ asset('images/happy-icon.png') }}" alt="Ikon Bahagia" class="me-3" style="width: 40px; height: 40px;">
                <div>
                    <h5 class="fw-bold">4. Memberikan Perasaan Bahagia</h5>
                    <p style="text-align: justify;">
                        Membantu sesama dengan donor darah dapat meningkatkan rasa empati, kepedulian, dan kebahagiaan secara psikologis.
                    </p>
                </div>
            </div>

            {{-- Tombol kembali ke halaman awal (home/dashboard) --}}
            <a href="{{ session('is_logged_in') ? route('dashboard') : route('guest.home') }}" class="btn btn-danger mt-3">← Kembali ke Beranda</a>
        </div>

        {{-- Ilustrasi --}}
        <div class="col-md-4 d-none d-md-block">
            <img src="{{ asset('images/ilustrasi-donor.png') }}" alt="Ilustrasi Donor" class="img-fluid">
        </div>
    </div>
</div>
@endsection

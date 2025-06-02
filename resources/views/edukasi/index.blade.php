@extends('layouts.app') 

@section('title', 'Manfaat Donor Darah')

@section('content')
<div class="container py-5">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="text-danger fw-bold">Manfaat Donor Darah</h1>
            <h5>Artikel tentang manfaat mendonorkan darah</h5>
            <p style="text-align: justify;">
            Donor darah tidak hanya menyelamatkan nyawa orang lain, tetapi juga membawa banyak manfaat kesehatan bagi pendonornya. Dengan mendonorkan darah secara rutin, kamu dapat membantu memperlancar aliran darah, menurunkan risiko penyakit jantung, serta merangsang pembentukan sel darah baru. Selain itu, donor darah juga menjadi bentuk empati sosial yang nyata dan mempererat solidaritas antar sesama.
            </p>
            <a href="{{ route('edukasi.detail') }}" class="btn btn-danger">Baca Selengkapnya</a>
        </div>
        <div class="col-md-6 text-center">
            <img src="{{ asset('images/donor-edukasi.png') }}" alt="Ilustrasi Donor" class="img-fluid" style="max-height: 300px;">
        </div>
    </div>
</div>
@endsection

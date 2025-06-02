<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EdukasiController extends Controller
{
    public function index()
    {
        return view('edukasi.index');
    }

    public function detail()
    {
        return view('edukasi.detail');
    }

}

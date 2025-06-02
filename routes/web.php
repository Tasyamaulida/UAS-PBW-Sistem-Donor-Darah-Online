<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PendonorController;
use App\Http\Controllers\PenerimaController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DonorRequestController;
use App\Http\Controllers\EdukasiController;

/*
|--------------------------------------------------------------------------
| Halaman Publik
|--------------------------------------------------------------------------
*/

// Halaman Landing Awal (tampil meskipun belum login)
Route::get('/', function () {
    return view('guest.home'); // berisi tombol login & register
})->name('guest.home');

// Halaman Tentang (boleh diakses siapa saja)
Route::get('/tentang', function () {
    return view('guest.tentang');
})->name('guest.tentang');

//Halaman Edukasi ( boleh diakses siapa aja)
Route::get('/edukasi', [EdukasiController::class, 'index'])->name('edukasi.index');
Route::get('/edukasi/manfaat-donor-darah', function () {
    return view('edukasi.detail');
})->name('edukasi.detail');


// Halaman Login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Halaman Register
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

/*
|--------------------------------------------------------------------------
| Proses Register dan Login ke Database
|--------------------------------------------------------------------------
*/

// Proses register
Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Silakan login.');
})->name('register.post');

// Proses login
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->with('error', 'Email atau kata sandi salah.');
})->name('login.post');

// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login')->with('success', 'Berhasil logout.');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Halaman Setelah Login (Dibatasi oleh Auth)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard Umum
    Route::get('/dashboard', function () {
        return view('dashboard.index', ['user_name' => Auth::user()->name]);
    })->name('dashboard');

    // Dashboard Pendonor
    Route::get('/dashboard/pendonor', [PendonorController::class, 'index'])->name('dashboard.pendonor');
    Route::get('/pendonor/create', [PendonorController::class, 'create'])->name('pendonor.create');
    Route::post('/pendonor/store', [PendonorController::class, 'store'])->name('pendonor.store');
    Route::get('/pendonor/{id}/edit', [PendonorController::class, 'edit'])->name('pendonor.edit');
    Route::put('/pendonor/{id}', [PendonorController::class, 'update'])->name('pendonor.update');
    Route::delete('/pendonor/{id}', [PendonorController::class, 'destroy'])->name('pendonor.destroy');
    Route::get('/pendonor/{id}', [PendonorController::class, 'show'])->name('pendonor.show');
    Route::post('/pendonor/{id}/kirim', [PendonorController::class, 'kirimPesan'])->name('pendonor.kirimPesan');
    Route::patch('/pendonor/{id}/status', [PendonorController::class, 'updateStatus'])->name('pendonor.updateStatus');
    Route::post('/donor/requests/{id}/accept', [DonorRequestController::class, 'accept'])->name('donor.requests.accept');
    Route::post('/donor/requests/{id}/decline', [DonorRequestController::class, 'decline'])->name('donor.requests.decline');
    Route::post('/donor/requests/{id}/complete', [DonorRequestController::class, 'complete'])->name('donor.requests.complete');
    Route::post('/donor-request/{id}/accept', [DonorRequestController::class, 'accept'])->name('donor.accept');
    Route::get('/cari', [PendonorController::class, 'index'])->name('pendonor.cari');
    Route::post('/cari', [PendonorController::class, 'search'])->name('pendonor.search');
    Route::post('/donor-requests', [DonorRequestController::class, 'store'])->name('donor.requests.store');
    Route::get('/permintaan/{id}', [DonorRequestController::class, 'show'])->name('permintaan.show');
    Route::get('/pendonor/terima-permintaan/{id}', [App\Http\Controllers\PendonorController::class, 'terimaPermintaan'])->name('pendonor.terima-permintaan');
    Route::get('/pendonor/tolak-permintaan/{id}', [App\Http\Controllers\PendonorController::class, 'tolakPermintaan'])->name('pendonor.tolak-permintaan');

    // Dashboard Penerima
    // Dashboard Penerima
    Route::get('/dashboard/penerima', [PenerimaController::class, 'index'])->name('dashboard.penerima');
    Route::resource('penerimas', PenerimaController::class);
    Route::get('/penerima/create', [PenerimaController::class, 'create'])->name('penerima.create');
    Route::get('/penerima/cari-pendonor', [PenerimaController::class, 'cariPendonor'])->name('penerimas.cariPendonor');
    Route::post('/penerima', [PenerimaController::class, 'store'])->name('penerima.store');
    Route::put('/penerima/{id}', [PenerimaController::class, 'update'])->name('penerima.update');
    Route::post('/penerimas/tambah-pendonor/{pendonor_id}', [PenerimaController::class, 'tambahPendonor'])->name('penerimas.tambahPendonor');
    Route::post('/penerima/store', [PenerimaController::class, 'store'])->name('penerima.store');
    
    
    //chat
    
    Route::middleware(['auth'])->group(function () {
        Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/chat/{user}', [ChatController::class, 'send'])->name('chat.send');
    });

    
});

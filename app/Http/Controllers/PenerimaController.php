<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penerima;
use App\Models\Pendonor;
use App\Models\User;
use App\Models\PermintaanDonor; // Model lama Anda
use App\Models\DonorRequest;    // Model BARU untuk alur Terima/Tolak
use Illuminate\Support\Facades\Auth; // Diganti dari auth() helper ke Facade
use Illuminate\Support\Facades\Log;   // Untuk logging
use App\Notifications\PermintaanBaruNotification; // Notifikasi yang akan menampilkan tombol Terima/Tolak

class PenerimaController extends Controller
{
    // ... (method index, create, store, edit, update, destroy Anda TIDAK BERUBAH) ...

    public function index()
    {
        $penerimas = Penerima::all(); // Ini yang membuat error "Undefined variable $penerimas" di view jika tidak ada variabel $penerimas
                                     // Jika dashboard penerima hanya untuk profil pribadi, ini harusnya:
                                     // $user = Auth::user();
                                     // $profilPenerima = $user->penerima()->first();
                                     // $permintaanDibuat = $profilPenerima ? DonorRequest::where('user_id', $user->id)->get() : collect();
                                     // return view('dashboard.penerima', compact('profilPenerima', 'permintaanDibuat', 'user'));
                                     // Tapi karena Anda bilang dashboard penerima menampilkan SEMUA data penerima:
        return view('dashboard.penerima', compact('penerimas')); // Pastikan view Anda mengharapkan $penerimas (plural)
    }

    public function create()
    {
        return view('penerima.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([ // Ganti nama variabel agar tidak konflik
            'nama' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'golongan_darah' => 'required|string|max:5',
            'asal_daerah' => 'required|string|max:255',
            'riwayat_transfusi' => 'nullable|string',
        ]);

        // Tambahkan user_id jika profil penerima terkait dengan user
        $user = Auth::user();
        if ($user && !$user->penerima()->exists()) { // Cek jika user belum punya profil
            Penerima::create(array_merge($validatedData, ['user_id' => $user->id]));
             return redirect()->route('dashboard.penerima')->with('success', 'Profil penerima berhasil disimpan.'); // Redirect ke dashboard penerima
        } elseif ($user && $user->penerima()->exists()) {
            return redirect()->route('dashboard.penerima')->with('info', 'Anda sudah memiliki profil penerima.');
        }
        // Jika tidak ada user login atau tidak terkait user, ini akan membuat profil umum
        // Penerima::create($validatedData);
        // return redirect()->route('dashboard.index')->with('success', 'Data penerima berhasil disimpan.');
        return redirect()->route('login')->with('error', 'Silakan login untuk membuat profil penerima.'); // Jika user tidak login
    }

    public function edit($id)
    {
        $penerima = Penerima::findOrFail($id);
        // Tambahkan otorisasi jika perlu (misal hanya user ybs atau admin yg bisa edit)
        return view('penerima.edit', compact('penerima'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([ // Ganti nama variabel
            'nama' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'golongan_darah' => 'required|string|max:5',
            'asal_daerah' => 'required|string|max:255',
            'riwayat_transfusi' => 'nullable|string',
        ]);

        $penerima = Penerima::findOrFail($id);
        // Tambahkan otorisasi
        $penerima->update($validatedData);

        return redirect()->route('dashboard.penerima')->with('success', 'Data berhasil diperbarui!'); // Asumsi ada route penerimas.index
    }

    public function destroy($id)
    {
        $penerima = Penerima::findOrFail($id);
        // Tambahkan otorisasi
        $penerima->delete();

        return redirect()->route('dashboard.penerima')->with('success', 'Data berhasil dihapus!'); // Asumsi ada route penerimas.index
    }

    public function cariPendonor()
    {
        $user = Auth::user(); // Menggunakan Auth facade

        // Logika untuk otomatis membuat profil penerima jika belum ada (mungkin perlu dipertimbangkan ulang)
        // Sebaiknya user diarahkan untuk membuat profil secara eksplisit.
        // if ($user && $user->role === 'penerima' && !$user->penerima()->exists()) {
        //     Penerima::create([ // Pastikan semua field required Penerima ada
        //         'user_id' => $user->id,
        //         'nama' => $user->name, // Contoh, ambil dari user
        //         // Anda perlu field lain seperti no_telp, golongan_darah, asal_daerah
        //     ]);
        //     $user->load('penerima'); // Refresh relasi
        // }

        // Pastikan user yang login memiliki profil penerima sebelum mencari
        if ($user && !$user->penerima()->exists()) {
             return redirect()->route('penerima.create')->with('info', 'Harap lengkapi profil penerima Anda terlebih dahulu.');
        }

        $donors = Pendonor::where('status', 'Tersedia') // Konsistenkan case 'Tersedia' atau 'tersedia'
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('penerima.cari-pendonor', compact('donors', 'user'));
    }

    public function tambahPendonor(Request $request, $pendonor_id_profil) // Ubah nama parameter agar jelas
    {
        Log::info('Request tambahPendonor (yang juga akan membuat DonorRequest) diterima', [
            'pendonor_profile_id' => $pendonor_id_profil,
            'user_penerima_id' => Auth::id(),
            'message_tambahan_from_form' => $request->input('message_tambahan') // Jika ada field ini di form
        ]);

        $userPenerima = Auth::user();
        $profilPenerima = $userPenerima->penerima()->first(); // Asumsi relasi 'penerima' di User model

        if (!$profilPenerima) {
            Log::warning('Profil penerima tidak ditemukan untuk user.', ['user_id' => $userPenerima->id]);
            return redirect()->route('penerima.create') // Arahkan ke form buat profil penerima
                             ->with('warning', 'Harap lengkapi profil penerima Anda terlebih dahulu.');
        }

        $profilPendonor = Pendonor::with('user')->find($pendonor_id_profil); // Eager load relasi user

        if (!$profilPendonor) {
            Log::error('Profil pendonor tidak ditemukan.', ['pendonor_profile_id' => $pendonor_id_profil]);
            return redirect()->back()->with('error', 'Pendonor yang Anda pilih tidak ditemukan.');
        }

        if (!$profilPendonor->user) {
            Log::error('Profil pendonor tidak memiliki User terkait.', ['pendonor_profile_id' => $profilPendonor->id]);
            return redirect()->back()->with('error', 'Pendonor ini tidak memiliki akun pengguna yang valid untuk menerima notifikasi.');
        }

        // --- Logika "Tambah Pendonor" lama Anda menggunakan PermintaanDonor (JIKA MASIH DIPERLUKAN) ---
        // $sudahAdaPermintaanLama = PermintaanDonor::where('penerima_id', $profilPenerima->id)
        //                                     ->where('pendonor_id', $profilPendonor->id)
        //                                     ->exists();
        // if ($sudahAdaPermintaanLama) {
        //      Log::info('PermintaanDonor (lama) sudah ada.', ['penerima_id' => $profilPenerima->id, 'pendonor_id' => $profilPendonor->id]);
        //     // return redirect()->back()->with('warning', 'Anda sudah pernah mengirim permintaan (lama) ke pendonor ini.');
        // } else {
        //     PermintaanDonor::create([
        //         'penerima_id' => $profilPenerima->id,
        //         'pendonor_id' => $profilPendonor->id, // Ini adalah ID dari model/tabel profil Pendonor
        //         'status' => 'pending', // atau status default untuk PermintaanDonor
        //     ]);
        //     Log::info('PermintaanDonor (lama) dibuat.', ['penerima_id' => $profilPenerima->id, 'pendonor_id' => $profilPendonor->id]);
        // }
        // --- Selesai Logika Lama ---


        // --- MULAI LOGIKA PEMBUATAN DonorRequest (BARU) untuk Notifikasi Terima/Tolak ---
        try {
            // Cek apakah sudah ada DonorRequest yang 'pending' atau 'diterima' dari penerima ini
            // untuk GOLONGAN DARAH yang sama. Ini adalah kebijakan opsional.
            // Sesuai diskusi terakhir, penerima bisa meminta lagi dari orang lain meski gol darah sama.
            // Jika ingin membatasi per PENDONOR SPESIFIK, ceknya akan berbeda.
            // Untuk saat ini, kita izinkan pembuatan DonorRequest baru.

            $donorRequestBaru = DonorRequest::create([
                'user_id' => $userPenerima->id,                   // ID User Penerima
                'blood_type' => $profilPendonor->golongan_darah, // Gol. darah dari profil pendonor yang "ditambahkan"
                'location' => $profilPendonor->asal_daerah,     // Lokasi dari profil pendonor
                'message' => $request->input('message_tambahan'),// Jika ada input 'message_tambahan' di form
                'status' => 'pending',
                // Jika ingin mencatat PENDONOR SPESIFIK yang dituju oleh permintaan awal ini:
                // 'target_pendonor_user_id' => $profilPendonor->user->id,
            ]);
            Log::info('DonorRequest (baru) berhasil dibuat.', ['donor_request_id' => $donorRequestBaru->id]);

            // Kirim notifikasi ke User yang memiliki profil pendonor ini
            // Notifikasi ini HARUS dikonfigurasi untuk menggunakan $donorRequestBaru->id
            $profilPendonor->user->notify(new PermintaanBaruNotification($donorRequestBaru));
            Log::info('Notifikasi PermintaanBaruNotification dikirim ke user pendonor.', [
                'pendonor_user_id' => $profilPendonor->user->id,
                'donor_request_id' => $donorRequestBaru->id
            ]);

            // Pesan sukses bisa digabungkan atau disesuaikan
            return redirect()->back()->with('success', 'Permintaan donor berhasil dikirimkan kepada ' . $profilPendonor->nama . '.');

        } catch (\Exception $e) {
            Log::error('Gagal membuat atau mengirim notifikasi untuk DonorRequest (baru).', [
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan teknis saat memproses permintaan donor Anda (DR).');
        }
        // --- SELESAI LOGIKA PEMBUATAN DonorRequest (BARU) ---
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Pendonor;
use App\Models\User;
use App\Models\PermintaanDonor;
use App\Models\Penerima; // Jika masih digunakan
use App\Models\Chat; // Jika masih digunakan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Lebih eksplisit untuk auth()
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\DonorRequest;

class PendonorController extends Controller
{
     public function index()
    {
        $user = Auth::user(); // Ambil user yang login

        // Mengambil profil pendonor milik user yang login
        // Kode Anda sebelumnya: $pendonors = Pendonor::where('user_id', Auth::id())->latest()->get();
        // Ini mengembalikan collection. Jika user hanya punya 1 profil, lebih baik ambil satu objek.
        // Jika bisa banyak dan Anda ingin menampilkan semuanya di tabel profil, maka $pendonors (plural) tetap ok.
        $pendonors = Pendonor::where('user_id', $user->id)->latest()->get();
        Log::info("[PendonorController:Index] Jumlah profil pendonor ditemukan untuk User ID {$user->id}: " . $pendonors->count());


        // Ambil Notifikasi permintaan BARU untuk pendonor ini
        // Logika ini sudah ada di view Anda dan berfungsi, jadi kita tidak perlu mengirimnya secara eksplisit dari sini
        // kecuali jika Anda ingin memindahkannya ke controller.
        // $unreadDonorRequestNotifications = $user->unreadNotifications->filter(function ($notification) {
        //     return $notification->type === 'App\\Notifications\\PermintaanBaruNotification' &&
        //            isset($notification->data['permintaan_id']);
        // });


        // BARU: Ambil DonorRequest yang telah DITERIMA atau SELESAI oleh pendonor ini
        // 'pendonor_id' di tabel 'donor_requests' adalah ID dari User pendonor
        $permintaanDitangani = DonorRequest::where('pendonor_id', $user->id)
                                        ->whereIn('status', ['diterima', 'selesai'])
                                        ->with('requester') // Eager load user penerima (pembuat permintaan)
                                        ->orderBy('updated_at', 'desc')
                                        ->get();
        Log::info("[PendonorController:Index] Jumlah permintaan ditangani (diterima/selesai) oleh User ID {$user->id}: " . $permintaanDitangani->count());

        return view('dashboard.pendonor', compact(
            'pendonors',            // Variabel yang sudah Anda gunakan untuk tabel profil
            'permintaanDitangani'   // BARU: Untuk daftar permintaan yang ditangani dengan tombol chat
            // 'unreadDonorRequestNotifications' // Tidak perlu jika logika sudah di view
        ));
    }

   public function create()
    {
        $existing = Pendonor::where('user_id', Auth::id())->first();
        if ($existing) {
            return redirect()->route('dashboard.pendonor')->with('warning', 'Anda sudah mengisi data pendonor sebelumnya.');
        }
        return view('pendonor.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama'           => 'required|string|max:255',
            'no_telp'        => 'required|string|max:20',
            'golongan_darah' => ['required', 'string', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'asal_daerah'    => 'required|string|max:255',
            'riwayat_donor'  => 'nullable|string',
        ]);
        $validatedData['user_id'] = Auth::id();
        $validatedData['status'] = 'Tersedia'; // Konsistenkan case jika perlu (misal, 'tersedia')
        Pendonor::create($validatedData);
        return redirect()->route('dashboard.pendonor')->with('success', 'Data pendonor berhasil disimpan.');
    }

    public function edit($id)
    {
        $pendonor = Pendonor::findOrFail($id);
        if ($pendonor->user_id !== Auth::id()) {
            abort(403, 'Anda tidak diizinkan untuk mengedit data ini.');
        }
        return view('pendonor.edit', compact('pendonor'));
    }

    public function update(Request $request, $id)
    {
        $pendonor = Pendonor::findOrFail($id);
        if ($pendonor->user_id !== Auth::id()) {
            abort(403, 'Anda tidak diizinkan untuk memperbarui data ini.');
        }
        $validatedData = $request->validate([
            'nama'           => 'required|string|max:255',
            'no_telp'        => ['required', 'string', 'max:20', Rule::unique('pendonors')->ignore($pendonor->id)],
            'golongan_darah' => ['required', 'string', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'asal_daerah'    => 'required|string|max:255',
            'riwayat_donor'  => 'nullable|string',
        ]);
        $pendonor->update($validatedData);
        return redirect()->route('dashboard.pendonor')->with('success', 'Data pendonor berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pendonor = Pendonor::findOrFail($id);
        if ($pendonor->user_id !== Auth::id()) {
            abort(403, 'Anda tidak diizinkan untuk menghapus data ini.');
        }
        $pendonor->delete();
        return redirect()->route('dashboard.pendonor')->with('success', 'Data pendonor berhasil dihapus.');
    }

    public function show($userId)
    {
        $user = User::findOrFail($userId);
        $pendonor = Pendonor::where('user_id', $user->id)->first();
        return view('profil.pendonor', compact('user', 'pendonor'));
    }

    public function kirimPesan(Request $request, $penerimaUserId)
    {
        $request->validate(['pesan' => 'required|string|max:1000']);
        Log::info("Pesan untuk User ID {$penerimaUserId} dari User ID ".Auth::id().": ".$request->pesan);
        return redirect()->back()->with('success', 'Pesan berhasil dikirim.');
    }

    public function updateStatus(Request $request, $pendonorId)
    {
        $request->validate(['status' => 'required|in:Tersedia,Tidak Tersedia']); // Sesuaikan case dengan data Anda
        $pendonor = Pendonor::findOrFail($pendonorId);
        if ($pendonor->user_id !== Auth::id()) {
            abort(403, 'Aksi tidak diizinkan.');
        }
        $pendonor->status = $request->status;
        $pendonor->save();
        return redirect()->back()->with('success', 'Status ketersediaan berhasil diperbarui.');
    }

    // Method terimaPermintaan dan tolakPermintaan LAMA Anda yang menggunakan model PermintaanDonor.
    // Ini tidak akan memicu alur Terima/Tolak dan Chat yang baru menggunakan DonorRequest.
    // Jika tombol di notifikasi sudah mengarah ke DonorRequestController, method ini mungkin tidak lagi dipanggil untuk alur utama.
    public function terimaPermintaan($permintaanId)
    {
        Log::warning("[PendonorController:terimaPermintaan LAMA] Method ini dipanggil untuk PermintaanDonor ID: {$permintaanId}. Ini mungkin method lama.");
        $permintaan = PermintaanDonor::with('penerima.user', 'pendonor.user')->findOrFail($permintaanId);
        if ($permintaan->pendonor->user_id !== Auth::id()) { abort(403, 'Aksi tidak diizinkan.'); }
        $permintaan->status = 'diterima';
        $permintaan->save();
        // Notifikasi lama Anda (jika ada)
        return redirect()->route('dashboard.pendonor')->with('success', 'Permintaan donor (sistem lama) diterima.');
    }

    public function tolakPermintaan($permintaanId)
    {
        Log::warning("[PendonorController:tolakPermintaan LAMA] Method ini dipanggil untuk PermintaanDonor ID: {$permintaanId}. Ini mungkin method lama.");
        $permintaan = PermintaanDonor::with('penerima.user', 'pendonor.user')->findOrFail($permintaanId);
        if ($permintaan->pendonor->user_id !== Auth::id()) { abort(403, 'Aksi tidak diizinkan.'); }
        $permintaan->status = 'ditolak';
        $permintaan->save();
        // Notifikasi lama Anda (jika ada)
        return redirect()->route('dashboard.pendonor')->with('success', 'Permintaan donor (sistem lama) telah ditolak.');
    }
}
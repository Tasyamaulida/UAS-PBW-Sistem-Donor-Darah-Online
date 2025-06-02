<?php

namespace App\Http\Controllers;

use App\Models\DonorRequest;
use App\Models\Pendonor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\DonorRequestNotification;
use App\Notifications\PermintaanDonorDiterima;
use App\Notifications\PermintaanDonorDitolak;
// use App\Notifications\PermintaanDonorSelesaiNotification; // Jika Anda membuat ini
use Illuminate\Support\Facades\Log; // PASTIKAN INI ADA

class DonorRequestController extends Controller
{
    /**
     * Menyimpan permintaan donor baru yang dibuat oleh Penerima.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'blood_type' => 'required|string|max:5',
            'location' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $donorRequest = DonorRequest::create([
                'user_id' => Auth::id(), // User pembuat permintaan (Penerima)
                'blood_type' => $validatedData['blood_type'],
                'location' => $validatedData['location'],
                'message' => $validatedData['message'],
                'status' => 'pending', // Status awal
            ]);
            Log::info("[Controller:Store] DonorRequest baru berhasil dibuat.", [
                'donor_request_id' => $donorRequest->id,
                'user_id_penerima' => Auth::id()
            ]);

            $matchedPendonorProfiles = Pendonor::where('golongan_darah', $donorRequest->blood_type)
                ->where('status', 'Tersedia')
                ->with('user')
                ->get();
            Log::info("[Controller:Store] Jumlah pendonor cocok ditemukan: " . $matchedPendonorProfiles->count(), [
                'donor_request_id' => $donorRequest->id
            ]);

            if ($matchedPendonorProfiles->isEmpty()) {
                Log::warning("[Controller:Store] Tidak ada pendonor yang cocok atau tersedia untuk DR ID: {$donorRequest->id} dengan Gol.Darah: {$donorRequest->blood_type}");
                // Opsional: Hapus $donorRequest jika tidak ada yang cocok sama sekali
                // $donorRequest->delete();
                // Log::info("[Controller:Store] DonorRequest ID: {$donorRequest->id} dihapus karena tidak ada pendonor cocok.");
                return redirect()->route('dashboard.penerima')
                                 ->with('warning', 'Permintaan donor Anda telah dibuat (ID: #'.$donorRequest->id.'), tetapi saat ini tidak ada pendonor yang cocok atau tersedia untuk golongan darah tersebut.');
            }

            foreach ($matchedPendonorProfiles as $pendonorProfile) {
                if ($pendonorProfile->user) {
                    Log::info("[Controller:Store] Mengirim notifikasi awal (DonorRequestNotification) ke Pendonor User ID: {$pendonorProfile->user->id} untuk DR ID: {$donorRequest->id}");
                    $pendonorProfile->user->notify(new DonorRequestNotification($donorRequest));
                } else {
                    Log::warning("[Controller:Store] Profil Pendonor ID: {$pendonorProfile->id} tidak memiliki User terkait, notifikasi tidak dikirim.");
                }
            }
            
            return redirect()->route('dashboard.penerima')
                             ->with('success', 'Permintaan donor (ID: #'.$donorRequest->id.') berhasil dikirim dan akan dinotifikasikan ke pendonor yang cocok.');

        } catch (\Exception $e) {
            Log::error('[Controller:Store] Gagal membuat permintaan donor: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                // 'trace' => $e->getTraceAsString() // Hati-hati, bisa sangat panjang
            ]);
            return back()->with('error', 'Terjadi kesalahan teknis saat membuat permintaan donor. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Aksi Pendonor menerima permintaan donor.
     * $id adalah ID dari DonorRequest.
     */
    public function accept($id)
    {
        Log::info("[Controller:Accept] PROSES DIMULAI - DonorRequest ID: {$id}");
        $loggedInUser = Auth::user(); // User pendonor yang sedang login

        DB::beginTransaction();
        try {
            $donorRequest = DonorRequest::with('requester')->lockForUpdate()->findOrFail($id);
            Log::info("[Controller:Accept] DonorRequest ID: {$id} ditemukan. Status saat ini: {$donorRequest->status}");

            if (!$donorRequest->requester) {
                Log::error("[Controller:Accept] GAGAL KRITIS: Requester (Penerima) tidak ditemukan untuk DR ID: {$id}. Rollback.");
                DB::rollBack();
                return redirect()->route('dashboard.pendonor')->with('error', 'Kesalahan: Data penerima permintaan tidak valid atau tidak ditemukan.');
            }
            Log::info("[Controller:Accept] Requester (Penerima) DITEMUKAN: User ID {$donorRequest->requester->id}, Nama: {$donorRequest->requester->name}, untuk DR ID: {$id}");

            if ($donorRequest->status !== 'pending') {
                Log::info("[Controller:Accept] Dihentikan, status bukan 'pending' untuk DR ID: {$id}. Status saat ini: {$donorRequest->status}. Rollback.");
                DB::rollBack();
                return redirect()->route('dashboard.pendonor')->with('info', 'Permintaan ini sudah direspon sebelumnya.');
            }

            $donorRequest->status = 'diterima';
            $donorRequest->pendonor_id = $loggedInUser->id;
            $donorRequest->save();
            Log::info("[Controller:Accept] DR ID: {$id} status diubah menjadi 'diterima', pendonor_id (User ID Pendonor): {$loggedInUser->id}");

            if ($loggedInUser->pendonor) {
                $pendonorProfile = $loggedInUser->pendonor()->first();
                if ($pendonorProfile) {
                    $pendonorProfile->status = 'Tidak Tersedia';
                    $pendonorProfile->save();
                    Log::info("[Controller:Accept] Status profil pendonor (Profil ID: {$pendonorProfile->id}, User ID: {$loggedInUser->id}) diubah menjadi 'Tidak Tersedia'");
                } else { Log::warning("[Controller:Accept] User pendonor (ID: {$loggedInUser->id}) tidak memiliki profil Pendonor terkait."); }
            } else { Log::warning("[Controller:Accept] User pendonor (ID: {$loggedInUser->id}) tidak memiliki relasi 'pendonor'."); }

            DB::commit();
            Log::info("[Controller:Accept] Transaksi commit untuk DR ID: {$id}");

            // Ambil ulang objek DonorRequest DARI DATABASE setelah commit, dengan relasi yang dibutuhkan notifikasi
            $freshDonorRequest = DonorRequest::with(['requester', 'donor'])->find($id);

            if (!$freshDonorRequest) {
                Log::error("[Controller:Accept] GAGAL SETELAH COMMIT: DonorRequest ID {$id} tidak ditemukan lagi. Ini seharusnya tidak terjadi. Menggunakan data lama jika memungkinkan.");
                // Jika fresh gagal, coba gunakan objek $donorRequest yang lama tapi pastikan relasi dimuat.
                $donorRequest->load(['requester', 'donor']); // Muat relasi pada objek lama sebagai fallback
                $objectUntukNotifDanRedirect = $donorRequest;
            } else {
                Log::info("[Controller:Accept] Data Fresh DonorRequest sebelum notifikasi: pendonor_id=" . ($freshDonorRequest->pendonor_id ?? 'NULL') . ", Relasi Donor valid? " . ($freshDonorRequest->donor ? 'YA (ID:'.$freshDonorRequest->donor->id.')' : 'TIDAK/NULL'));
                $objectUntukNotifDanRedirect = $freshDonorRequest;
            }

            // Pastikan requester masih ada
            if ($objectUntukNotifDanRedirect->requester) {
                Log::info("[Controller:Accept] Akan mengirim Notifikasi DITERIMA ke User ID: {$objectUntukNotifDanRedirect->requester->id} untuk DR ID: {$objectUntukNotifDanRedirect->id}");
                $objectUntukNotifDanRedirect->requester->notify(new PermintaanDonorDiterima($objectUntukNotifDanRedirect));
                Log::info("[Controller:Accept] Notifikasi DITERIMA seharusnya sudah dikirim ke User ID: {$objectUntukNotifDanRedirect->requester->id}");

                // Redirect ke Chat: Pendonor (user login) akan chat dengan Requester (penerima)
                // Pastikan kedua ID ada sebelum membuat route
                if ($objectUntukNotifDanRedirect->requester->id && $objectUntukNotifDanRedirect->id) {
                     Log::info("[Controller:Accept] Data untuk redirect ke chat: user (requester_id)={$objectUntukNotifDanRedirect->requester->id}, request_id={$objectUntukNotifDanRedirect->id}");
                    return redirect()->route('chat.show', [
                                         'user' => $objectUntukNotifDanRedirect->requester->id, // Target chat adalah penerima
                                         'request_id' => $objectUntukNotifDanRedirect->id
                                     ])
                                     ->with('success', 'Anda telah menerima permintaan donor. Silakan mulai percakapan dengan penerima.');
                } else {
                    Log::error("[Controller:Accept] GAGAL REDIRECT KE CHAT. Requester ID atau DonorRequest ID tidak valid.", ['donor_request_data' => $objectUntukNotifDanRedirect->toArray()]);
                    return redirect()->route('dashboard.pendonor')->with('warning', 'Permintaan diterima, tetapi terjadi masalah saat menyiapkan data untuk mengarahkan Anda ke halaman chat (ID tidak valid).');
                }
            } else {
                Log::error("[Controller:Accept] GAGAL KIRIM NOTIF/REDIRECT: Requester tidak ditemukan pada objek DonorRequest untuk DR ID: {$objectUntukNotifDanRedirect->id}");
                return redirect()->route('dashboard.pendonor')->with('success', 'Permintaan diterima, namun terjadi kesalahan saat memproses data penerima.');
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error("[Controller:Accept] GAGAL: DonorRequest ID {$id} tidak ditemukan (ModelNotFound). Error: " . $e->getMessage());
            return redirect()->route('dashboard.pendonor')->with('error', 'Permintaan donor tidak ditemukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[Controller:Accept] GAGAL: Error Umum untuk DR ID: {$id}. Message: " . $e->getMessage(), ['exception_class' => get_class($e), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('dashboard.pendonor')->with('error', 'Terjadi kesalahan teknis saat menerima permintaan donor.');
        }
    }

    /**
     * Aksi Pendonor menolak permintaan donor.
     * $id adalah ID dari DonorRequest.
     */
    public function decline($id)
    {
        Log::info("[Controller:Decline] PROSES DIMULAI - DonorRequest ID: {$id}");
        $donorRequest = DonorRequest::with('requester')->findOrFail($id);

        if (!$donorRequest->requester) {
            Log::error("[Controller:Decline] GAGAL: Requester TIDAK DITEMUKAN untuk DonorRequest ID: {$id}");
            return redirect()->route('dashboard.pendonor')->with('error', 'Kesalahan: Data penerima permintaan tidak ditemukan.');
        }
        Log::info("[Controller:Decline] Requester DITEMUKAN: User ID {$donorRequest->requester->id}, Nama: {$donorRequest->requester->name}, untuk DR ID: {$id}");

        if ($donorRequest->status !== 'pending') {
            Log::info("[Controller:Decline] Dihentikan, status bukan 'pending' untuk DR ID: {$id}. Status saat ini: {$donorRequest->status}");
            return redirect()->route('dashboard.pendonor')->with('info', 'Permintaan ini sudah direspon sebelumnya.');
        }

        try {
            $donorRequest->status = 'ditolak';
            $donorRequest->save();
            Log::info("[Controller:Decline] DR ID: {$id} status diubah menjadi 'ditolak'");

            Log::info("[Controller:Decline] Akan mengirim Notifikasi DITOLAK ke User ID: {$donorRequest->requester->id} untuk DR ID: {$id}");
            $donorRequest->requester->notify(new PermintaanDonorDitolak($donorRequest));
            Log::info("[Controller:Decline] Notifikasi DITOLAK seharusnya sudah dikirim.");

            return redirect()->route('dashboard.pendonor')
                             ->with('success', 'Permintaan donor telah ditolak.');
        } catch (\Exception $e) {
            Log::error("[Controller:Decline] GAGAL: Error Umum untuk DR ID: {$id}. Message: " . $e->getMessage(), ['exception_class' => get_class($e), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('dashboard.pendonor')->with('error', 'Terjadi kesalahan teknis saat menolak permintaan.');
        }
    }

    /**
     * Aksi Pendonor menandai permintaan donor sebagai selesai.
     * $id adalah ID dari DonorRequest.
     */
    public function complete($id)
    {
        Log::info("[Controller:Complete] PROSES DIMULAI - DonorRequest ID: {$id}");
        $loggedInUser = Auth::user();
        $donorRequest = DonorRequest::with(['requester', 'donor'])->findOrFail($id);

        if ($donorRequest->pendonor_id !== $loggedInUser->id || $donorRequest->status !== 'diterima') {
            Log::warning("[Controller:Complete] GAGAL: User tidak berhak atau status tidak valid.", [
                'DR_id' => $id, 'logged_in_user_id' => $loggedInUser->id,
                'DR_pendonor_id' => $donorRequest->pendonor_id, 'DR_status' => $donorRequest->status
            ]);
            return redirect()->back()->with('error', 'Anda tidak berhak menandai permintaan ini sebagai selesai atau status tidak valid.');
        }

        try {
            $donorRequest->status = 'selesai';
            $donorRequest->save();
            Log::info("[Controller:Complete] DR ID: {$id} status diubah menjadi 'selesai'");

            if ($loggedInUser->pendonor) {
                $pendonorProfile = $loggedInUser->pendonor()->first();
                if ($pendonorProfile) {
                    $pendonorProfile->status = 'Tersedia';
                    $pendonorProfile->save();
                    Log::info("[Controller:Complete] Status profil pendonor (Profil ID: {$pendonorProfile->id}) diubah kembali menjadi 'Tersedia'");
                }
            }

            // Opsional: Kirim notifikasi ke penerima bahwa donor telah selesai
            // if ($donorRequest->requester) {
            //     Log::info("[Controller:Complete] Akan mengirim notifikasi PermintaanDonorSelesai ke User ID: {$donorRequest->requester->id}");
            //     $donorRequest->requester->notify(new \App\Notifications\PermintaanDonorSelesaiNotification($donorRequest));
            //     Log::info("[Controller:Complete] Notifikasi PermintaanDonorSelesai seharusnya sudah dikirim.");
            // }

            return redirect()->route('dashboard.pendonor')->with('success', 'Proses donor telah ditandai sebagai selesai.');

        } catch (\Exception $e) {
            Log::error("[Controller:Complete] GAGAL: Error Umum untuk DR ID: {$id}. Message: " . $e->getMessage(), ['exception_class' => get_class($e), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('dashboard.pendonor')->with('error', 'Terjadi kesalahan saat menandai selesai.');
        }
    }
}
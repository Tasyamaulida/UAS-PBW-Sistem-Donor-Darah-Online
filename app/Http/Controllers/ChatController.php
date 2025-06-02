<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\DonorRequest;
use Illuminate\Http\Request as HttpRequest; // Alias untuk HttpRequest
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;     // PASTIKAN INI ADA

class ChatController extends Controller
{
    /**
     * Menampilkan halaman chat antara dua user, dengan konteks DonorRequest jika ada.
     */
    public function show(HttpRequest $httpRequest, $userId) // $userId adalah ID user lawan bicara
    {
        $currentUser = Auth::user();
        $otherUser = User::find($userId);

        if (!$otherUser) {
            Log::warning("[ChatController:Show] GAGAL: User lawan bicara dengan ID: {$userId} tidak ditemukan.");
            return redirect()->route('dashboard')->with('error', 'Pengguna yang Anda tuju untuk chat tidak ditemukan.');
        }

        $donorRequest = null;
        $isCurrentUserPendonor = false;
        $canAccessChat = false;

        $donorRequestIdFromQuery = $httpRequest->query('request_id');
        Log::info("[ChatController:Show] Mencoba akses chat.", [
            'currentUser_id' => $currentUser->id, 'currentUser_name' => $currentUser->name,
            'otherUser_id' => $otherUser->id, 'otherUser_name' => $otherUser->name,
            'request_id_from_query' => $donorRequestIdFromQuery
        ]);

        if ($donorRequestIdFromQuery) {
            $donorRequest = DonorRequest::with(['requester', 'donor'])->find($donorRequestIdFromQuery);
            if (!$donorRequest) {
                Log::warning("[ChatController:Show] GAGAL: DonorRequest dengan ID: {$donorRequestIdFromQuery} tidak ditemukan meskipun ada di query.");
                return redirect()->route('dashboard')->with('error', 'Detail permintaan donor terkait chat tidak ditemukan.');
            }
            Log::info("[ChatController:Show] DonorRequest ID: {$donorRequest->id} ditemukan via query parameter. Status: {$donorRequest->status}");
        } else {
            // Fallback jika tidak ada request_id (kurang ideal, lebih baik selalu ada request_id dari URL notifikasi/redirect)
            Log::info("[ChatController:Show] request_id tidak ada di query. Mencoba fallback untuk mencari DR 'diterima' atau 'selesai' terakhir.");
            $donorRequest = DonorRequest::whereIn('status', ['diterima', 'selesai'])
                ->where(function ($query) use ($currentUser, $otherUser) {
                    $query->where('pendonor_id', $currentUser->id)
                          ->where('user_id', $otherUser->id); // currentUser adalah pendonor, otherUser adalah requester
                })
                ->orWhere(function ($query) use ($currentUser, $otherUser) {
                    $query->where('pendonor_id', $otherUser->id)
                          ->where('user_id', $currentUser->id); // currentUser adalah requester, otherUser adalah pendonor
                })
                ->orderBy('updated_at', 'desc')
                ->with(['requester', 'donor'])
                ->first();
            if ($donorRequest) {
                Log::info("[ChatController:Show] Fallback menemukan DonorRequest ID: {$donorRequest->id} dengan status: {$donorRequest->status}");
            } else {
                Log::info("[ChatController:Show] Fallback tidak menemukan DonorRequest yang relevan antara User ID {$currentUser->id} dan {$otherUser->id}.");
            }
        }

        // Validasi akses ke chat
        if ($donorRequest) {
            $isCurrentUserTheRequester = $donorRequest->user_id === $currentUser->id;
            $isCurrentUserTheAssignedPendonor = $donorRequest->pendonor_id === $currentUser->id;

            if ($isCurrentUserTheAssignedPendonor) {
                $isCurrentUserPendonor = true; // Untuk tombol "Selesai Donor" di view
            }

            // User bisa akses chat jika:
            // 1. Mereka adalah requester (penerima) ATAU pendonor yang ditugaskan.
            // 2. DAN status request adalah 'diterima' ATAU 'selesai' (untuk melihat riwayat).
            if (($isCurrentUserTheRequester || $isCurrentUserTheAssignedPendonor) &&
                in_array($donorRequest->status, ['diterima', 'selesai'])) {
                // Pastikan juga otherUser (dari parameter URL) adalah partisipan yang benar
                if (($isCurrentUserTheRequester && $donorRequest->pendonor_id === $otherUser->id) ||
                    ($isCurrentUserTheAssignedPendonor && $donorRequest->user_id === $otherUser->id)) {
                    $canAccessChat = true;
                    Log::info("[ChatController:Show] Akses chat DIIZINKAN untuk User ID {$currentUser->id}.", [
                        'DR_id' => $donorRequest->id, 'status_DR' => $donorRequest->status
                    ]);
                } else {
                     Log::warning("[ChatController:Show] Akses chat DITOLAK: otherUser (ID: {$otherUser->id}) bukan partisipan valid di DR ID: {$donorRequest->id}. Detail DR:", $donorRequest->toArray());
                }
            } else {
                Log::warning("[ChatController:Show] Akses chat DITOLAK untuk User ID {$currentUser->id} karena status atau partisipasi tidak sesuai.", [
                    'DR_id' => $donorRequest->id, 'status_DR' => $donorRequest->status,
                    'isRequester' => $isCurrentUserTheRequester, 'isAssignedPendonor' => $isCurrentUserTheAssignedPendonor
                ]);
            }
        } else {
            // Jika tidak ada DonorRequest yang terkait (misalnya, jika request_id tidak ada & fallback gagal)
            Log::info("[ChatController:Show] Tidak ada DonorRequest yang terkait untuk chat ini (request_id: {$donorRequestIdFromQuery}).");
            // Jika $donorRequestIdFromQuery ada tapi $donorRequest null, berarti ID salah/DR dihapus
            if ($donorRequestIdFromQuery) { // Hanya redirect jika memang ada upaya akses dengan ID
                 return redirect()->route('dashboard')->with('error', 'Detail permintaan donor untuk chat ini tidak ditemukan atau tidak valid.');
            }
            // Jika tidak ada request_id sama sekali dan tidak ada fallback, $canAccessChat tetap false.
        }

        if (!$canAccessChat) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke percakapan ini atau permintaan sudah tidak valid.');
        }

        // Ambil pesan
        $messagesQuery = Message::where(function ($query) use ($currentUser, $otherUser) {
                $query->where('from_user_id', $currentUser->id)->where('to_user_id', $otherUser->id);
            })->orWhere(function ($query) use ($currentUser, $otherUser) {
                $query->where('from_user_id', $otherUser->id)->where('to_user_id', $currentUser->id);
            });

        // Selalu filter pesan berdasarkan donor_request_id jika $donorRequest ada dan valid
        if ($donorRequest && $donorRequest->id) { // Pastikan $donorRequest dan ID nya ada
             $messagesQuery->where('donor_request_id', $donorRequest->id);
             Log::info("[ChatController:Show] Pesan difilter berdasarkan donor_request_id: " . $donorRequest->id);
        } else {
            // Jika $donorRequest tidak ada (seharusnya tidak sampai sini jika $canAccessChat true dan chat harus terkait DR),
            // cegah pengambilan semua pesan antar dua user jika pesan harus terikat DR.
            Log::warning("[ChatController:Show] DonorRequest tidak valid saat mengambil pesan. Ini seharusnya tidak terjadi jika akses chat diizinkan dan chat harus terkait DR.");
            $messagesQuery->whereRaw('1 = 0'); // Tidak akan mengembalikan hasil
        }

        $messages = $messagesQuery->with('sender')->orderBy('created_at', 'asc')->get();
        Log::info("[ChatController:Show] Jumlah pesan yang diambil: " . $messages->count());

        // Ambil URL kembali dari query ATAU tentukan berdasarkan role user yang login
        $redirectBackUrl = $httpRequest->query('redirect_url');
        if (!$redirectBackUrl) {
            if (Auth::check()) { // Seharusnya selalu true karena ada middleware auth
                $userRole = $currentUser->role; // Asumsi ada kolom 'role' di model User
                if ($userRole === 'pendonor') {
                    $redirectBackUrl = route('dashboard.pendonor');
                } elseif ($userRole === 'penerima') {
                    $redirectBackUrl = route('dashboard.penerima');
                } else {
                    $redirectBackUrl = route('dashboard'); // Default
                }
            } else {
                $redirectBackUrl = route('dashboard'); // Fallback
            }
        }
        Log::info("[ChatController:Show] redirectBackUrl ditentukan sebagai: " . $redirectBackUrl);

        return view('chat.show', compact(
            'messages',
            'otherUser',
            'donorRequest',
            'isCurrentUserPendonor',
            'currentUser',
            'redirectBackUrl'
        ));
    }

    /**
     * Mengirim pesan.
     */
    public function send(HttpRequest $httpRequest, $userId) // $userId adalah ID penerima pesan
    {
        $validatedData = $httpRequest->validate([
            'message' => 'required|string|max:1000',
            'donor_request_id' => 'nullable|exists:donor_requests,id',
            // 'redirect_url' => 'nullable|url' // Tidak perlu validasi URL di sini jika hanya untuk redirect
        ]);

        $currentUser = Auth::user();
        $otherUser = User::find($userId);

        if (!$otherUser) {
            Log::warning("[ChatController:Send] GAGAL: Penerima pesan ID {$userId} tidak ditemukan.");
            return redirect()->back()->with('error', 'Penerima pesan tidak ditemukan.');
        }

        // Validasi tambahan: Pastikan user boleh mengirim pesan dalam konteks DonorRequest ini (jika ada)
        if (!empty($validatedData['donor_request_id'])) {
            $donorRequest = DonorRequest::find($validatedData['donor_request_id']);
            if ($donorRequest) {
                $isRequester = $donorRequest->user_id === $currentUser->id;
                $isAssignedPendonor = $donorRequest->pendonor_id === $currentUser->id;
                // Pastikan otherUser adalah partisipan yang benar
                $isValidParticipantForOtherUser = ($isRequester && $donorRequest->pendonor_id === $otherUser->id) ||
                                           ($isAssignedPendonor && $donorRequest->user_id === $otherUser->id);

                // Hanya boleh kirim pesan jika status DonorRequest adalah 'diterima'
                // dan currentUser serta otherUser adalah partisipan yang sah.
                if (!($isRequester || $isAssignedPendonor) || !$isValidParticipantForOtherUser || $donorRequest->status !== 'diterima') {
                    Log::warning("[ChatController:Send] Upaya mengirim pesan DITOLAK.", [
                        'sender_id' => $currentUser->id, 'receiver_id' => $otherUser->id,
                        'DR_id' => $donorRequest->id, 'DR_status' => $donorRequest->status,
                        'isRequester' => $isRequester, 'isAssignedPendonor' => $isAssignedPendonor,
                        'isValidParticipantForOtherUser' => $isValidParticipantForOtherUser
                    ]);
                    return redirect()->back()->with('error', 'Tidak dapat mengirim pesan untuk permintaan ini atau status tidak valid.');
                }
                 Log::info("[ChatController:Send] Validasi pengiriman pesan untuk DR ID {$donorRequest->id} BERHASIL.");
            } else {
                Log::warning("[ChatController:Send] GAGAL: donor_request_id ({$validatedData['donor_request_id']}) dikirim tapi DR tidak ditemukan.");
                return redirect()->back()->with('error', 'Konteks permintaan donor untuk chat tidak valid.');
            }
        } else {
            Log::warning("[ChatController:Send] Pesan dikirim tanpa donor_request_id (chat umum?).");
            // Jika chat Anda harus selalu terkait DonorRequest, Anda bisa melarang ini:
            // return redirect()->back()->with('error', 'Konteks permintaan donor dibutuhkan untuk chat.');
        }

        Message::create([
            'from_user_id' => $currentUser->id,
            'to_user_id' => $userId,
            'message' => $validatedData['message'],
            'donor_request_id' => $validatedData['donor_request_id'] ?? null,
        ]);
        Log::info("[ChatController:Send] Pesan terkirim dari User ID {$currentUser->id} ke User ID {$userId}.", [
            'donor_request_id' => $validatedData['donor_request_id'] ?? null
        ]);

        // Redirect kembali ke halaman chat, pertahankan request_id dan redirect_url jika ada
        $redirectParams = ['user' => $userId];
        if (!empty($validatedData['donor_request_id'])) {
            $redirectParams['request_id'] = $validatedData['donor_request_id'];
        }
        // Ambil redirect_url dari input form (yang mungkin dikirim dari show method)
        if ($httpRequest->filled('redirect_url_hidden_input')) { // Ganti nama input jika berbeda
            $redirectParams['redirect_url'] = $httpRequest->input('redirect_url_hidden_input');
        } elseif ($httpRequest->query('redirect_url')) { // Atau jika masih ada di query string URL action
             $redirectParams['redirect_url'] = $httpRequest->query('redirect_url');
        }
        Log::info("[ChatController:Send] Redirecting ke chat.show dengan parameter:", $redirectParams);

        return redirect()->route('chat.show', $redirectParams)->with('message_sent', true);
    }
}
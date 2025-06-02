<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
// Hapus baris ini jika Anda menonaktifkan antrian untuk debugging:
// use Illuminate\Contracts\Queue\ShouldQueue; 
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DonorRequest; // Pastikan model ini ada dan benar path-nya
use App\Models\User;       // Digunakan untuk type-hinting jika perlu
use Illuminate\Support\Facades\Log; // Untuk logging

// Hapus 'implements ShouldQueue' jika menonaktifkan antrian untuk debugging
class PermintaanDonorDiterima extends Notification /* implements ShouldQueue */
{
    use Queueable; // Queueable bisa tetap ada

    protected DonorRequest $donorRequest;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\DonorRequest $donorRequest
     */
    public function __construct(DonorRequest $donorRequest)
    {
        $this->donorRequest = $donorRequest;
        Log::info('[PermintaanDonorDiterima] Konstruktor dipanggil.', [
            'donor_request_id' => $this->donorRequest->id,
            'requester_id' => $this->donorRequest->user_id, // ID User Penerima
            'pendonor_id' => $this->donorRequest->pendonor_id // ID User Pendonor
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable // Ini adalah objek User Penerima
     * @return array
     */
    public function via($notifiable): array
    {
        Log::info('[PermintaanDonorDiterima] Method via() dipanggil.', [
            'notifiable_id' => $notifiable->id, // ID User Penerima
            'donor_request_id' => $this->donorRequest->id
        ]);
        // Pastikan 'database' ada agar tersimpan dan bisa ditampilkan di dashboard penerima
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable // Ini adalah objek User Penerima
     * @return \Illuminate\Notifications\Messages\MailMessage|null
     */
    public function toMail($notifiable)
    {
        Log::info('[PermintaanDonorDiterima] Method toMail() dipanggil.', [
            'notifiable_id' => $notifiable->id,
            'donor_request_id' => $this->donorRequest->id
        ]);

        try {
            // $this->donorRequest->donor adalah relasi ke model User pendonor
            // Pastikan relasi 'donor' di model DonorRequest sudah benar
            // public function donor() { return $this->belongsTo(User::class, 'pendonor_id'); }
            $pendonorUser = $this->donorRequest->donor;
            $pendonorName = 'Seorang Pendonor'; // Default
            $pendonorIdForChat = null;

            if ($pendonorUser) {
                $pendonorName = $pendonorUser->name;
                $pendonorIdForChat = $pendonorUser->id;
                Log::info('[PermintaanDonorDiterima] Data pendonor untuk email: ', ['id' => $pendonorIdForChat, 'nama' => $pendonorName]);
            } else {
                Log::warning('[PermintaanDonorDiterima] Gagal mendapatkan objek User Pendonor dari relasi donor pada DonorRequest ID: ' . $this->donorRequest->id . '. Kolom pendonor_id mungkin null atau relasi salah.');
            }

            $chatUrl = '#'; // Default URL jika pendonor tidak ditemukan
            if ($pendonorIdForChat) {
                // URL untuk penerima agar bisa chat dengan pendonor
                // Menyertakan 'user' (ID pendonor) dan 'request_id' (ID DonorRequest)
                $chatUrl = route('chat.show', [
                    'user' => $pendonorIdForChat,       // Lawan bicara penerima adalah pendonor
                    'request_id' => $this->donorRequest->id // Konteks permintaan
                ]);
                Log::info('[PermintaanDonorDiterima] URL Chat untuk email berhasil dibuat: ' . $chatUrl);
            } else {
                Log::warning('[PermintaanDonorDiterima] Tidak dapat membuat URL Chat untuk email karena ID Pendonor tidak tersedia.', ['donor_request_id' => $this->donorRequest->id]);
            }

            return (new MailMessage)
                        ->subject('Kabar Baik! Permintaan Donor Darah Anda Diterima')
                        ->greeting('Halo ' . $notifiable->name . ',') // $notifiable->name adalah nama Penerima
                        ->line("Permintaan donor darah Anda untuk golongan {$this->donorRequest->blood_type} (Permintaan #{$this->donorRequest->id}) telah diterima oleh {$pendonorName}.")
                        ->action('Mulai Chat dengan Pendonor', $chatUrl)
                        ->line('Silakan segera berkoordinasi dengan pendonor melalui fitur chat untuk langkah selanjutnya.');
        } catch (\Exception $e) {
            Log::error('[PermintaanDonorDiterima] ERROR di toMail(): ' . $e->getMessage(), [
                'file' => $e->getFile(), 'line' => $e->getLine(), 'donor_request_id' => $this->donorRequest->id
            ]);
            // Opsional: Kirim email pemberitahuan error sederhana jika gagal membuat email utama
            return (new MailMessage)->error()->subject('Error Notifikasi Sistem')->line('Terjadi kesalahan internal saat mencoba mengirimkan email notifikasi persetujuan permintaan donor.');
        }
    }

    /**
     * Get the array representation of the notification (untuk disimpan di tabel 'notifications').
     *
     * @param  mixed  $notifiable // Ini adalah objek User Penerima
     * @return array
     */
    public function toArray($notifiable): array
    {
        Log::info('[PermintaanDonorDiterima] Method toArray() dipanggil.', [
            'notifiable_id' => $notifiable->id, // ID User Penerima
            'donor_request_id' => $this->donorRequest->id
        ]);

        try {
            $pendonorUser = $this->donorRequest->donor; // Relasi ke User model pendonor
            $pendonorName = 'Seorang Pendonor'; // Default
            $pendonorIdUntukNotif = null;
            $chatUrlUntukNotif = '#'; // Default

            if ($pendonorUser) {
                $pendonorName = $pendonorUser->name;
                $pendonorIdUntukNotif = $pendonorUser->id;
                Log::info('[PermintaanDonorDiterima] Data pendonor untuk array notifikasi: ', ['id' => $pendonorIdUntukNotif, 'nama' => $pendonorName]);

                // Membuat URL chat
                $chatUrlUntukNotif = route('chat.show', [
                    'user' => $pendonorIdUntukNotif,       // Lawan bicara penerima adalah pendonor
                    'request_id' => $this->donorRequest->id // Konteks permintaan
                ]);
                Log::info('[PermintaanDonorDiterima] URL Chat untuk data array notifikasi dibuat: ' . $chatUrlUntukNotif);

            } else {
                Log::warning('[PermintaanDonorDiterima] Gagal mendapatkan objek User Pendonor dari relasi donor pada DonorRequest ID: ' . $this->donorRequest->id . ' untuk data array notifikasi. Kolom pendonor_id mungkin null atau relasi salah.');
            }

            $dataToStore = [
                'permintaan_id' => $this->donorRequest->id, // ID dari DonorRequest
                'status' => 'diterima',                     // Status permintaan
                'pesan' => "Kabar baik! Permintaan donor darah Anda (Gol. {$this->donorRequest->blood_type}, Permintaan #{$this->donorRequest->id}) telah diterima oleh {$pendonorName}.",
                'pendonor_id' => $pendonorIdUntukNotif,     // ID User Pendonor yang menerima
                'pendonor_nama' => $pendonorName,           // Nama Pendonor yang menerima
                'url_chat' => $chatUrlUntukNotif,           // URL untuk tombol "Chat" di dashboard penerima
                'created_at' => now()->toDateTimeString(),  // Waktu notifikasi ini dibuat
            ];
            Log::info('[PermintaanDonorDiterima] Data toArray yang akan dikembalikan: ', $dataToStore);
            return $dataToStore;

        } catch (\Exception $e) {
            Log::error('[PermintaanDonorDiterima] ERROR di toArray(): ' . $e->getMessage(), [
                'file' => $e->getFile(), 'line' => $e->getLine(), 'donor_request_id' => $this->donorRequest->id
            ]);
            // Mengembalikan array minimal jika ada error untuk mencegah kegagalan total penyimpanan notifikasi
            return [
                'permintaan_id' => $this->donorRequest->id,
                'status' => 'diterima', // Tetap tandai diterima
                'pesan' => "Permintaan Anda (ID: {$this->donorRequest->id}) telah diterima, namun terjadi masalah saat memproses detail notifikasi.",
                'error_flag_internal' => true, // Flag untuk menandakan ada masalah internal
                'error_message_debug_internal' => $e->getMessage() // Untuk debugging jika perlu
            ];
        }
    }
}
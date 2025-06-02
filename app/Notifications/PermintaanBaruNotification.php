<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DonorRequest; // PENTING: Gunakan model DonorRequest

class PermintaanBaruNotification extends Notification // implements ShouldQueue (jika pakai antrian)
{
    use Queueable;

    public DonorRequest $donorRequest; // Tipe diubah menjadi DonorRequest

    /**
     * Create a new notification instance.
     *
     * @param DonorRequest $donorRequest Objek DonorRequest yang baru dibuat
     */
    public function __construct(DonorRequest $donorRequest)
    {
        $this->donorRequest = $donorRequest;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail']; // Sesuaikan (misal hanya 'database')
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable) // $notifiable adalah User Pendonor
    {
        $namaPemohon = $this->donorRequest->requester ? $this->donorRequest->requester->name : 'Seseorang';
        $urlToDashboard = route('dashboard.pendonor');

        return (new MailMessage)
                    ->subject('Anda Menerima Permintaan Donor Darah Baru')
                    ->greeting('Halo ' . $notifiable->name . ',')
                    ->line("Anda baru saja menerima permintaan donor darah dari {$namaPemohon}.")
                    ->line("Golongan Darah yang Dibutuhkan: {$this->donorRequest->blood_type}")
                    ->lineIf($this->donorRequest->message, "Pesan dari Pemohon: \"{$this->donorRequest->message}\"")
                    ->action('Lihat Permintaan di Dashboard', $urlToDashboard)
                    ->line('Mohon segera merespon permintaan ini melalui dashboard Anda.');
    }

    /**
     * Get the array representation of the notification (untuk disimpan di database).
     * Ini yang akan dibaca oleh pendonor.blade.php
     */
    public function toArray($notifiable): array
    {
        $namaPemohon = $this->donorRequest->requester ? $this->donorRequest->requester->name : 'Seseorang';
        return [
            // PENTING: 'permintaan_id' sekarang harus dari ID DonorRequest
            'permintaan_id' => $this->donorRequest->id,
            'pesan' => 'Ada permintaan donor (Gol. ' . $this->donorRequest->blood_type . ') dari ' . $namaPemohon . '.',
            'pemohon_nama' => $namaPemohon,
            'gol_darah' => $this->donorRequest->blood_type,
            'created_at' => now()->toDateTimeString(), // Waktu notifikasi dibuat
        ];
    }
}
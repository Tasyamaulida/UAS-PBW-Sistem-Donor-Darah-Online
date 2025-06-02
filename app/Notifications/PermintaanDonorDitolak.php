<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage; // Opsional
use Illuminate\Notifications\Notification;
use App\Models\DonorRequest; // Menggunakan model DonorRequest

class PermintaanDonorDitolak extends Notification implements ShouldQueue
{
    use Queueable;

    protected DonorRequest $donorRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(DonorRequest $donorRequest)
    {
        $this->donorRequest = $donorRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['database', 'mail']; // Sesuaikan channel
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) // $notifiable adalah User Penerima
    {
        $pendonorName = $this->donorRequest->donor ? $this->donorRequest->donor->name : 'Pendonor';
        return (new MailMessage)
                    ->subject('Informasi Permintaan Donor Darah Anda')
                    ->greeting('Halo ' . $notifiable->name . ',')
                    ->line("Maaf, permintaan donor darah Anda untuk golongan {$this->donorRequest->blood_type} belum dapat dipenuhi saat ini oleh {$pendonorName}.")
                    ->line('Anda dapat mencoba membuat permintaan baru nanti atau menunggu notifikasi dari pendonor lain.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array // $notifiable adalah User Penerima
    {
        $pendonorName = $this->donorRequest->donor ? $this->donorRequest->donor->name : 'Pendonor yang merespon';
        return [
            'permintaan_id' => $this->donorRequest->id,
            'pendonor_nama' => $pendonorName, // Jika ingin menampilkan siapa yang menolak
            'pesan' => "Maaf, permintaan donor darah Anda (gol. {$this->donorRequest->blood_type}) ditolak oleh {$pendonorName}.",
            'status' => 'ditolak',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
<?php

namespace App\Notifications;

use App\Models\DonorRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonorRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public DonorRequest $donorRequest;

    public function __construct(DonorRequest $donorRequest)
    {
        $this->donorRequest = $donorRequest;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Sesuaikan channel
    }

    public function toMail($notifiable) // $notifiable adalah User Pendonor
    {
        // URL bisa ke detail permintaan atau dashboard pendonor
        $url = route('dashboard.pendonor'); 
        $requesterName = $this->donorRequest->requester ? $this->donorRequest->requester->name : 'Seseorang';

        return (new MailMessage)
                    ->subject('Permintaan Donor Darah Baru')
                    ->greeting('Halo ' . $notifiable->name . ',')
                    ->line("Ada permintaan donor darah baru dari {$requesterName} yang mungkin cocok untuk Anda:")
                    ->line('Golongan Darah: ' . $this->donorRequest->blood_type)
                    ->line('Lokasi: ' . $this->donorRequest->location)
                    ->lineIf($this->donorRequest->message, 'Pesan dari pemohon: ' . $this->donorRequest->message)
                    ->action('Lihat di Dashboard', $url)
                    ->line('Jika Anda bersedia, silakan respon melalui dashboard Anda.');
    }

    public function toArray($notifiable) // Untuk notifikasi database di dashboard pendonor
    {
        $requesterName = $this->donorRequest->requester ? $this->donorRequest->requester->name : 'Seseorang';
        return [
            'permintaan_id' => $this->donorRequest->id, // Ini PENTING untuk tombol Terima/Tolak
            'pemohon_nama' => $requesterName,
            'gol_darah' => $this->donorRequest->blood_type,
            'lokasi' => $this->donorRequest->location,
            'pesan' => "Permintaan donor (gol. {$this->donorRequest->blood_type}) dari {$requesterName} di {$this->donorRequest->location}.",
            'created_at' => $this->donorRequest->created_at->toDateTimeString(),
        ];
    }
}
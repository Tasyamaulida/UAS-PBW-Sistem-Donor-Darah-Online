<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // pastikan modelnya sesuai

class DonorController extends Controller
{
    public function show($id)
    {
        $donor = User::findOrFail($id);
        return view('donor.show', compact('donor'));
    }

    public function accept($id)
    {
        $donorRequest = DonorRequest::findOrFail($id);
        $penerima = $donorRequest->user;
    
        // Opsional: ubah status pendonor jadi tidak tersedia
        $pendonor = auth()->user()->pendonor;
        if ($pendonor) {
            $pendonor->update(['status' => 'Tidak Tersedia']);
        }
    
        // Redirect ke halaman chat (pastikan route chat.show udah tersedia)
        return redirect()->route('chat.show', $penerima->id)
                         ->with('success', 'Permintaan diterima. Silakan lanjutkan percakapan.');
    }
    
}

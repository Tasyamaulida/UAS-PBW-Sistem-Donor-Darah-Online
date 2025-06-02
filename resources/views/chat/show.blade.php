@extends('layouts.app')

@section('title', 'Chat dengan ' . $otherUser->name)

@push('styles')
{{-- CSS Khusus untuk Halaman Chat --}}
<style>
    .chat-card-header {
        background-color: #dc3545;
        color: white;
        position: relative;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    .chat-header-icon-container {
        background-color: white;
        border-radius: 8px;
        padding: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
    }
    .chat-header-main-icon {
        color: #dc3545;
        font-size: 1.4em;
    }
    .chat-header-drop-icon {
        color: white;
        font-size: 0.4em;
        position: absolute;
        top: 42%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .header-decoration-icon-wrapper {
        position: absolute;
        right: 110px;
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .header-decoration-main-icon {
        color: white;
        font-size: 2.5em;
        opacity: 0.5;
    }
    .chat-card-header h5 {
        font-size: 1.1rem;
    }
    .chat-card-header small.header-subtext {
        font-size: 0.8rem;
        opacity: 0.9;
        line-height: 1.2;
    }
    .btn-kembali-custom {
        background-color: white;
        color: #dc3545;
        font-weight: 600;
        padding: 0.3rem 0.8rem;
        border-radius: 6px;
        border: none;
        font-size: 0.875rem;
    }
    .btn-kembali-custom:hover {
        background-color: #f1f1f1;
        color: #c82333;
    }

    .chat-box-container {
        height: 450px;
        overflow-y: auto;
        display: flex;
        flex-direction: column-reverse;
        background-color: #ffffff;
        padding: 1rem;
    }
    .chat-messages-wrapper {
        display: flex;
        flex-direction: column;
    }
    .chat-message-row {
        display: flex;
        margin-bottom: 0.75rem;
    }
    .chat-message-row.sent {
        justify-content: flex-end;
    }
    .chat-message-row.received {
        justify-content: flex-start;
    }

    .chat-message-bubble {
        padding: 0.5rem 0.8rem;  /* Padding sedikit dikurangi untuk "pas" */
        border-radius: 16px;   /* Bisa disesuaikan lagi */
        max-width: 70%;        /* Maksimum lebar bubble, sesuaikan jika perlu */
        word-wrap: break-word;
        white-space: pre-wrap;
        /* KUNCI: Membuat lebar bubble menyesuaikan konten */
        width: -webkit-fit-content; /* Safari, Chrome */
        width: -moz-fit-content;    /* Firefox */
        width: fit-content;         /* Standard */
        /* box-shadow akan diterapkan di kelas .sent dan .received */
    }
    .chat-message-bubble.sent {
        background-color: #FFF9E0;
        color: #5c502d;
        box-shadow: 0 1px 1px rgba(0,0,0,0.08);
    }
    .chat-message-bubble.received {
        background-color: #f0f0f0; /* Ubah ke abu-abu muda agar kontras dengan background chat putih */
        color: #333;
        box-shadow: 0 1px 1px rgba(0,0,0,0.08);
    }
    .message-sender-name {
        font-size: 0.8rem;
        font-weight: bold;
        margin-bottom: 0.2rem;
        color: #555;
    }
    .chat-message-bubble p {
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
        line-height: 1.4;
        text-align: left;
    }
    .chat-time {
        font-size: 0.7rem;
        color: #888;
        text-align: right;
        margin-top: 0.15rem;
    }
    .chat-message-bubble.sent .chat-time {
        color: #a08d5f;
    }
     .chat-message-bubble.received .chat-time {
        color: #777;
    }


    .chat-card-footer {
        background-color: #f0f0f0;
        border-top: 1px solid #ddd;
        padding: 0.75rem 1rem;
    }
    .chat-card-footer .form-control.chat-input {
        border-radius: 6px 0 0 6px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        border-right: none;
    }
     .chat-card-footer .form-control.chat-input:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        border-right: none;
    }
    .chat-card-footer .btn-danger {
        border-radius: 0 6px 6px 0;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
        border-left: none;
    }

    .chat-box-container::-webkit-scrollbar {
        width: 8px;
    }
    .chat-box-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .chat-box-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    .chat-box-container::-webkit-scrollbar-thumb:hover {
        background: #aeaeae;
    }

    .btn-complete-donor-wrapper {
        text-align: center;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }
    .btn-complete-donor {
        font-size: 1rem;
        font-weight: 500;
        padding: 0.65rem 2rem;
        border-radius: 8px;
        background-color: #198754;
        border-color: #198754;
        min-width: 300px;
        color: white;
    }
    .btn-complete-donor:hover {
        background-color: #157347;
        border-color: #146c43;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header chat-card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="chat-header-icon-container me-3">
                            <i class="fas fa-tint chat-header-main-icon"></i>
                            <i class="fas fa-circle chat-header-drop-icon"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Chat dengan: {{ $otherUser->name }}</h5>
                            @if($donorRequest)
                                <small class="header-subtext d-block">
                                    Terkait Permintaan #{{ $donorRequest->id }}
                                    (Gol. {{ $donorRequest->blood_type }})
                                    - Status: <span class="fw-semibold">{{ ucfirst($donorRequest->status) }}</span>
                                </small>
                            @endif
                        </div>
                    </div>
                    <div class="header-decoration-icon-wrapper">
                        <i class="fas fa-tint header-decoration-main-icon"></i>
                    </div>
                    <div class="chat-header-actions">
                        <a href="{{ $redirectBackUrl ?? route('dashboard') }}" class="btn btn-sm btn-kembali-custom">
                            Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="chat-box" class="chat-box-container">
                        <div class="chat-messages-wrapper">
                            @forelse ($messages as $message)
                                <div class="chat-message-row {{ $message->from_user_id == $currentUser->id ? 'sent' : 'received' }}">
                                    <div class="chat-message-bubble {{ $message->from_user_id == $currentUser->id ? 'sent' : 'received' }}">
                                        @if ($message->from_user_id != $currentUser->id && $donorRequest)
                                            {{-- Jika ingin menampilkan nama pengirim, aktifkan baris ini
                                            <small class="message-sender-name d-block">{{ $message->sender->name }}</small>
                                            --}}
                                        @endif
                                        <p class="mb-0">{{ $message->message }}</p>
                                        <div class="chat-time">
                                            {{ $message->created_at->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted mt-auto p-5">
                                    <i class="fas fa-comments fa-3x mb-2 text-black-50"></i>
                                    <p>Belum ada pesan. Mulai percakapan!</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card-footer chat-card-footer p-3">
                    @if($donorRequest && $donorRequest->status === 'diterima')
                        <form action="{{ route('chat.send', ['user' => $otherUser->id]) }}" method="POST" id="message-form">
                            @csrf
                            @if($donorRequest)
                                <input type="hidden" name="donor_request_id" value="{{ $donorRequest->id }}">
                            @endif
                            @if(isset($redirectBackUrl) && $redirectBackUrl)
                                <input type="hidden" name="redirect_url" value="{{ $redirectBackUrl }}">
                            @endif
                            <div class="input-group">
                                <input type="text" name="message" class="form-control chat-input" placeholder="Ketik pesan..." autofocus autocomplete="off" required>
                                <button type="submit" class="btn btn-danger">
                                    Kirim
                                </button>
                            </div>
                            @error('message') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                        </form>
                    @elseif($donorRequest && $donorRequest->status === 'selesai')
                        <p class="text-center text-muted mb-0"><i class="fas fa-check-circle text-success me-1"></i> Percakapan untuk permintaan ini telah selesai.</p>
                    @elseif($donorRequest && $donorRequest->status === 'ditolak')
                        <p class="text-center text-muted mb-0"><i class="fas fa-times-circle text-danger me-1"></i> Permintaan ini telah ditolak, percakapan tidak dapat dilanjutkan.</p>
                    @else
                        <p class="text-center text-muted mb-0">Tidak dapat mengirim pesan untuk percakapan ini saat ini.</p>
                    @endif
                </div>
            </div>

            @if($donorRequest && $isCurrentUserPendonor && $donorRequest->status === 'diterima')
                <div class="btn-complete-donor-wrapper">
                    <form action="{{ route('donor.requests.complete', ['id' => $donorRequest->id]) }}" method="POST" class="d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-complete-donor">
                            Tandai Proses Donor Selesai
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatBox = document.getElementById('chat-box');
        function scrollToBottom() {
            chatBox.scrollTop = 0;
        }
        scrollToBottom();

        @if(session('message_sent'))
            scrollToBottom();
        @endif

        // Mengaktifkan skrip Echo
        const currentUserId = {{ Auth::id() }};
        const otherUserId = {{ $otherUser->id }};
        // Pastikan $donorRequest ada sebelum mengakses ->id
        const donorRequestId = {{ $donorRequest ? $donorRequest->id : 'null' }};

        if (typeof Echo !== 'undefined' && donorRequestId) {
            console.log(`Listening on chat.donorrequest.${donorRequestId}`);
            Echo.private(`chat.donorrequest.${donorRequestId}`)
                .listen('.NewMessageEvent', (e) => {
                    console.log('Pesan baru diterima via Echo:', e);

                    // Pastikan data pesan ada dan bukan dari user saat ini
                    if (e.message && e.message.from_user_id != currentUserId) {
                        const messageWrapper = chatBox.querySelector('.chat-messages-wrapper');
                        const newMsgRow = document.createElement('div');
                        newMsgRow.classList.add('chat-message-row', 'received');

                        const senderNameHtml = e.sender_name ? `<small class="message-sender-name d-block">${e.sender_name}</small>` : '';

                        newMsgRow.innerHTML = `
                            <div class="chat-message-bubble received">
                                ${senderNameHtml}
                                <p class="mb-0">${e.message.message}</p>
                                <div class="chat-time text-end">${new Date(e.message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                            </div>
                        `;
                        // Karena flex-direction: column-reverse, insertBefore firstChild akan menempatkannya di bagian bawah secara visual
                        messageWrapper.insertBefore(newMsgRow, messageWrapper.firstChild);
                        scrollToBottom();
                    }
                });
        } else {
            if (typeof Echo === 'undefined') {
                console.log('Echo is not defined. Real-time chat will not work.');
            }
            if (!donorRequestId) {
                console.log('Donor Request ID is not available. Real-time chat for this request will not work.');
            }
        }
    });
</script>
@endpush
@extends('layout.template')

@section('title', 'Pendaftaran Berhasil')

@section('content')
<style>
    /* Confetti effect */
    .confetti {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 9999;
    }

    .confetti-piece {
        position: absolute;
        width: 10px;
        height: 10px;
        background-color: #ffc107;
        animation: fall 3s linear infinite;
    }

    @keyframes fall {
        0% {
            transform: translateY(0) rotate(0);
            opacity: 1;
        }
        100% {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }

    .bg-celebration {
        background: linear-gradient(135deg, #e0ffe0, #f0fff0);
    }
</style>

<div class="confetti" id="confetti-container"></div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 bg-celebration text-center">
                <div class="card-body py-5">
                    <h1 class="display-4 text-success fw-bold">🎉 Selamat!</h1>
                    <p class="lead mt-3">Pendaftaran Anda sebagai <strong>Member LaundryKu</strong> berhasil.</p>
                    <p class="mb-4">Saldo awal Anda telah ditambahkan dan membership kini aktif.</p>
                    <a href="{{ route('member.index') }}" class="btn btn-lg btn-success px-4">
                        ← Kembali ke Daftar Member
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple confetti generator
    const confettiContainer = document.getElementById('confetti-container');
    for (let i = 0; i < 100; i++) {
        const confetti = document.createElement('div');
        confetti.classList.add('confetti-piece');
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.animationDelay = Math.random() * 3 + 's';
        confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 70%)`;
        confettiContainer.appendChild(confetti);
    }
</script>
@endsection

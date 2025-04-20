@extends('layout.template')
@section('title', 'Top Up Saldo Member')
@section('content')
    <div class="container">
        <h4 class="my-3">{{ $isAlreadyMember ? 'Top Up Saldo' : 'Top Up Pendaftaran Member' }}</h4>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Masukkan Jumlah Top Up</h5>
            </div>
            <div class="card-body">
                {{-- Tempat untuk notifikasi error/sukses AJAX --}}
                <div id="payment-messages" style="display: none;"></div>

                {{-- Tampilkan error validasi Bawaan Laravel jika ada redirect back --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- Form hanya untuk input amount, submit ditangani JS --}}
                <form action="{{route('member.topup.initiate')}}" method="POST">
                    @csrf {{-- CSRF tetap perlu untuk request AJAX --}}
                    <input type="hidden" name="Id" value="{{Auth::user()->id}}">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah Top Up (Rp)</label>
                        <input type="number" name="amount" id="amount" class="form-control form-control-lg"
                               {{-- Set nilai awal: jika belum member, pakai minTopup, jika sudah, biarkan kosong (atau pakai old value) --}}
                               value="{{ old('amount', !$isAlreadyMember ? $minTopup : '') }}"
                               {{-- Atribut min hanya berlaku jika BELUM menjadi member --}}
                               @if (!$isAlreadyMember)
                                   min="{{ $minTopup }}"
                               @else
                                   min="1" {{-- Atau Anda bisa hapus atribut min jika benar-benar tidak ada minimal (misal boleh 0, tapi umumnya minimal 1) --}}
                               @endif
                               {{-- Placeholder disesuaikan --}}
                               placeholder="{{ !$isAlreadyMember ? 'Min. Rp ' . number_format($minTopup, 0, ',', '.') : 'Masukkan Jumlah Top Up' }}"
                               required>

                        {{-- Teks informasi disesuaikan --}}
                        @if (!$isAlreadyMember)
                            <div class="form-text text-danger">Minimal Rp {{ number_format($minTopup, 0, ',', '.') }}
                                untuk pendaftaran. Biaya pendaftaran (Rp {{ number_format($minTopup, 0, ',', '.') }})
                                akan dipotong dari top up ini.</div>
                        @else
                            {{-- Tidak menampilkan minimal topup jika sudah member --}}
                            <div class="form-text">Masukkan jumlah top up yang diinginkan.</div>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" id="btnPay">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"
                            style="display: none;"></span>
                        Lanjutkan ke Pembayaran
                    </button>
                    <a href="{{ route('member.index') }}" class="btn btn-secondary btn-lg">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Jika Anda menggunakan Javascript untuk validasi tambahan atau AJAX, pastikan logikanya juga disesuaikan --}}
<script>
    // Contoh: Jika Anda punya validasi JS sebelum submit
    // const form = document.querySelector('form');
    // const amountInput = document.getElementById('amount');
    // const isAlreadyMember = {{ $isAlreadyMember ? 'true' : 'false' }};
    // const minTopup = {{ $minTopup }};

    // form.addEventListener('submit', function(event) {
    //     const amount = parseInt(amountInput.value);
    //     if (!isAlreadyMember && amount < minTopup) {
    //         alert('Minimal top up untuk pendaftaran adalah Rp ' + minTopup.toLocaleString('id-ID'));
    //         event.preventDefault(); // Hentikan submit
    //     } else if (isAlreadyMember && amount <= 0) { // Validasi dasar jika sudah member
    //          alert('Jumlah top up harus lebih dari 0');
    //          event.preventDefault(); // Hentikan submit
    //     }
    //     // Tambahkan logika AJAX Anda di sini jika ada
    // });
</script>
@endpush
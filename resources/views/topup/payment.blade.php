@extends('layout.template')

@section('title', 'Pembayaran')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">

            <h2 class="fw-bold text-primary">Sedang Mengarahkan ke Pembayaran...</h2>
            <p class="text-muted">Mohon tunggu, Anda akan diarahkan ke halaman pembayaran Midtrans.</p>

        </div>
    </div>
</div>

<!-- Midtrans Snap.js -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('CLIENTKEY_MIDTRANS') }}"></script>
<script type="text/javascript">
    // Otomatis jalankan pembayaran begitu halaman ini dimuat
    snap.pay('{{ $snapToken }}', {
        onSuccess: function(result){
            window.location.href = "{{ url('/topup/success') }}";
        },
        onPending: function(result){
            alert("Pembayaran sedang diproses.");
        },
        onError: function(result){
            alert("Pembayaran gagal: " + JSON.stringify(result));
        }
    });
</script>
@endsection

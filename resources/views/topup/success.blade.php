@extends('layout.template')

@section('title', 'Top Up Berhasil')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="text-center mb-4">
                <h2 class="fw-bold text-success">Top Up Saldo Berhasil!</h2>
                <p class="text-muted">Pembayaran Anda telah berhasil diproses dan saldo Anda sudah diperbarui.</p>
            </div>
            @php
            $cabangInfo = session('assigned_cabang'); // Ambil data Cabang dari session
            $globalSetting = null; // Inisialisasi
            if (!$cabangInfo) {
                // Jika tidak ada info cabang di session, ambil setting global
                $globalSetting = \App\Models\Setting::first();
            }
            @endphp
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h5 class="mb-3">Terima kasih telah melakukan top up di <strong> {{ $globalSetting->nama_perusahaan ?? 'null'}}</strong>!</h5>
                    <p class="mb-0">Silakan lanjutkan pemesanan atau cek saldo Anda di dashboard.</p>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ url('/topupsaldo/'. Auth::user()->customer->id) }}" class="btn btn-success px-4">Kembali</a>
            </div>

        </div>
    </div>
</div>
@endsection

@extends('layout.template') 
@section('title', 'Top Up Saldo')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- Header --}}
            <div class="d-flex justify-center align-items-center mb-4">
                <h3 class="fw-bold text-primary mb-0">
                    <i class="bi bi-wallet2 me-2"></i>Top Up Saldo
                </h3>
            </div>
            <p class="text-muted mb-4 text-center">Isi ulang saldo akun kamu untuk melanjutkan transaksi melalui Midtrans 💰</p>


            {{-- Card Form --}}
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <form action="{{ url('/topupStore') }}" method="POST">
                        @csrf
                        <input type="hidden" name="Id" value="{{ Auth::user()->id }}">

                        {{-- Nama --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" value="{{ $users->user->email ?? '-' }}" readonly>
                        </div>

                        {{-- Nominal --}}
                        <div class="mb-4">
                            <label for="amount" class="form-label fw-semibold">Nominal Top Up</label>
                            <input type="number" name="amount" class="form-control" placeholder="Contoh: 50000" min="10000" required>
                            <small class="text-muted d-block mt-1">* Minimal top up Rp 10.000</small>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="btn btn-primary  shadow-sm">
                            <i class="bi bi-credit-card-2-front me-2"></i> Lanjutkan Pembayaran
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

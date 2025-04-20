@extends('layout.template')
@section('title', 'Status Membership')
@section('content')
<div class="container">
    <h4 class="my-3">Status Membership Anda</h4>
    <div class="card">
        <div class="card-body">
            @if($memberInfo && $memberInfo->isActive())
                <div class="alert alert-success">
                    <h5 class="alert-heading">Anda adalah Member Aktif!</h5>
                    <p>Tanggal Bergabung: {{ $memberInfo->joined_at ? $memberInfo->joined_at->format('d F Y') : $memberInfo->created_at->format('d F Y') }}</p>
                    <hr>
                    <p class="mb-0">Saldo Anda saat ini: <strong>Rp {{ number_format(Auth::user()->customer->saldo, 0, ',', '.') }}</strong></p>
                </div>
                <a href="{{ route('member.topup.form') }}" class="btn btn-primary">
                    <i class="bx bx-plus-circle"></i> Top Up Saldo
                </a>
            @elseif($memberInfo && !$memberInfo->isActive())
                 <div class="alert alert-warning">
                    <h5 class="alert-heading">Membership Belum Aktif</h5>
                    <p>Saldo Anda saat ini: <strong>Rp {{ number_format($memberInfo->balance, 0, ',', '.') }}</strong>.</p>
                    <p>Biaya pendaftaran member adalah Rp {{ number_format($membershipFee, 0, ',', '.') }}. Saldo Anda belum mencukupi.</p>
                    <hr>
                    <p class="mb-0">Silakan lakukan top up lagi untuk mengaktifkan membership.</p>
                 </div>
                 <a href="{{ route('member.topup.form') }}" class="btn btn-primary">
                    <i class="bx bx-plus-circle"></i> Top Up Lagi
                </a>
            @elseif($canBecomeMember)
                 <div class="alert alert-info">
                    <h5 class="alert-heading">Ingin Jadi Member?</h5>
                    <p>Dapatkan berbagai keuntungan dengan menjadi member. Biaya pendaftaran awal adalah <strong>Rp {{ number_format($membershipFee, 0, ',', '.') }}</strong>.</p>
                     <p>Lakukan top up pertama Anda sekarang (minimal Rp {{ number_format($membershipFee, 0, ',', '.') }}), biaya pendaftaran akan otomatis dipotong dan sisanya menjadi saldo Anda.</p>
                </div>
                 <a href="{{ route('member.topup.form') }}" class="btn btn-success">
                    <i class="bx bx-diamond"></i> Daftar & Top Up Sekarang
                </a>
            @else
                 <div class="alert alert-secondary">
                    <p>Fitur membership saat ini hanya tersedia untuk level pengguna.</p>
                 </div>
            @endif
        </div>
    </div>
</div>
@endsection
@extends('front.layout.template')

@section('title', 'Riwayat Saldo')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">Riwayat Saldo</h2>
                <p class="text-muted">Berikut daftar transaksi saldo Anda</p>
            </div>

            @if($users->saldoHistories->isEmpty())
                <div class="alert alert-info text-center">
                    Belum ada transaksi saldo.
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    {{-- <th>Deskripsi</th> --}}
                                    <th>Jumlah</th>
                                    <th>Jenis</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users->saldoHistories->sortByDesc('created_at') as $history)
                                <tr>
                                    <td>{{ $history->created_at->format('d M Y H:i') }}</td>
                                    {{-- <td>{{ $history->description }}</td> --}}
                                    <td>Rp {{ number_format($history->amount, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge {{ $history->type == 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $history->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="text-center mt-4">
                <a href="{{ url('/order') }}" class="btn btn-outline-primary">Kembali</a>
            </div>

        </div>
    </div>
</div>
@endsection

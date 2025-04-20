@extends('layout.template') {{-- Sesuaikan layout Anda --}}

@section('title', 'History Top Up')

@section('content')
<div class="container">
    <h4 class="my-3">History Top Up</h4>

    {{-- Form Filter (Hanya untuk Admin/Supervisor) --}}
    @if(in_array(Auth::user()->level, ['admin', 'supervisor']))
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('history.topup.index') }}" id="filterFormTopup">
                <div class="row g-2">
                    <div class="col-md-5">
                        <label for="search" class="form-label">Cari Nama / Email Pengguna:</label>
                        {{-- Tampilkan query pencarian sebelumnya --}}
                        <input type="text" class="form-control" id="search" name="search" value="{{ $searchQuery ?? '' }}" placeholder="Masukkan nama atau email...">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-search"></i> Cari
                        </button>
                    </div>
                     <div class="col-md-2 align-self-end">
                         <a href="{{ route('history.topup.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
    {{-- Akhir Form Filter --}}


    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
           <span>Daftar Transaksi Top Up</span>
           {{-- Tampilkan Total Top Up --}}
           <span class="badge bg-success fs-6">
                Total Top Up: Rp {{ number_format($totalTopup ?? 0, 0, ',', '.') }}
           </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            {{-- Tampilkan kolom User hanya untuk Admin/Supervisor --}}
                            @if(in_array(Auth::user()->level, ['admin', 'supervisor']))
                                <th>Nama Pengguna</th>
                                <th>Email</th>
                            @endif
                            <th>Deskripsi</th>
                            <th class="text-end">Jumlah (Rp)</th>
                            <th>Status</th>
                            <th>Metode</th>
                            <th>Ref ID Gateway</th>
                            <th>Diproses Pada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Tentukan colspan berdasarkan role untuk pesan @empty
                            $colspan = in_array(Auth::user()->level, ['admin', 'supervisor']) ? 10 : 8;
                        @endphp
                        @forelse ($topupHistory as $index => $transaction)
                        <tr>
                            <td>{{ $topupHistory->firstItem() + $index }}</td>
                            <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                             @if(in_array(Auth::user()->level, ['admin', 'supervisor']))
                                <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                                <td>{{ $transaction->user->email ?? 'N/A' }}</td>
                            @endif
                            <td>{{ $transaction->description }}</td>
                            <td class="text-end">{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                            <td>
                                @php /* Logika badge status */ @endphp
                                @php
                                    $statusClass = 'secondary';
                                    if (in_array($transaction->status, ['paid', 'completed', 'settlement'])) { $statusClass = 'success'; }
                                    elseif ($transaction->status == 'pending') { $statusClass = 'warning text-dark'; }
                                    elseif (in_array($transaction->status, ['failed', 'failure', 'error', 'deny', 'expired', 'cancelled'])) { $statusClass = 'danger'; }
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($transaction->status) }}</span>
                            </td>
                            <td>{{ $transaction->payment_gateway ?? '-' }}</td>
                            <td>{{ $transaction->gateway_ref_id ?? '-' }}</td>
                            <td>{{ $transaction->processed_at ? $transaction->processed_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $colspan }}" class="text-center">
                                @if($searchQuery)
                                    Tidak ada data history top up ditemukan untuk pencarian "{{ $searchQuery }}".
                                @else
                                    Tidak ada data history top up ditemukan.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Link Pagination (Sudah otomatis membawa query filter) --}}
            <div class="mt-3">
                {{ $topupHistory->links() }}
            </div>

        </div>
    </div>
</div>
@endsection
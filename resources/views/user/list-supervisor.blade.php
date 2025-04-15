{{-- Contoh: resources/views/users/list-supervisor.blade.php --}}
@extends('layout.template')

@section('title', 'Daftar Supervisor')

@section('content')
<div class="container">
    <h4 class="my-3">Daftar Pengguna - Level Supervisor</h4>

    <div class="card">
        <div class="card-header">List Supervisor</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th>Tgl Dibuat</th>
                            {{-- Mungkin tidak perlu tombol Aksi di sini jika aksi diurus di halaman user utama --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($supervisors as $supervisor)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $supervisor->name }}</td>
                            <td>{{ $supervisor->email }}</td>
                            <td>{{ $supervisor->phone ?? '-' }}</td>
                            <td>
                                @if($supervisor->status)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>{{ $supervisor->created_at->format('d-m-Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data supervisor ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
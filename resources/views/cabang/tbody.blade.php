@forelse ($cabangs as $cabang)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $cabang->kode_cabang }}</td>
    <td>{{ $cabang->nama_perusahaan }}</td>
    <td>{{ Str::limit($cabang->alamat_perusahaan, 50) }}</td>
    <td>{{ $cabang->kelurahan_perusahaan ?? '-' }}</td> 
    <td>{{ $cabang->kecamatan_perusahaan ?? '-' }}</td> 
    <td>{{ $cabang->kota_perusahaan ?? '-' }}</td>
    <td>{{ $cabang->provinsi_perusahaan ?? '-' }}</td> 
    <td>{{ $cabang->kode_pos ?? '-' }}</td>>
    <td>
        @if ($cabang->logo_perusahaan)
            <img src="{{ asset('storage/' . $cabang->logo_perusahaan) }}" width="80"
                height="40" style="object-fit: contain;"
                alt="Logo {{ $cabang->nama_perusahaan }}">
        @else
            <span class="text-muted fst-italic">N/A</span>
        @endif
    </td>
    <td>
        @if ($cabang->status)
            <span class="badge bg-success">Aktif</span>
        @else
            <span class="badge bg-danger">Nonaktif</span>
        @endif
    </td>
    <td>
        {{-- Tombol Edit --}}
        <button class="btn btn-warning btn-sm btn-edit-cabang"
            title="Edit {{ $cabang->nama_perusahaan }}"
            data-cabang="{{ json_encode($cabang) }}">
            <i class="fas fa-pencil-alt"></i>
        </button>
        {{-- Tombol Hapus --}}
        <button class="btn btn-danger btn-sm btn-hapus-cabang"
            title="Hapus {{ $cabang->nama_perusahaan }}" data-id="{{ $cabang->id }}"
            data-cabang="{{ json_encode($cabang) }}"> {{-- Tambahkan data-cabang juga --}}
            <i class="fas fa-trash-alt"></i>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center">Tidak ada data cabang ditemukan.</td>
</tr>
@endforelse
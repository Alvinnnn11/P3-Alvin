{{-- File: resources/views/layanan/tbody.blade.php --}}
@forelse ($layanans as $item)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $item->nama_layanan }}</td>
    {{-- Format harga sebagai Rupiah --}}
    <td>{{ 'Rp ' . number_format($item->harga_per_unit, 0, ',', '.') }}</td>
    {{-- Tampilkan nama satuan dari relasi, cek jika relasi ada --}}
    <td>{{ $item->satuan->nama_satuan ?? 'N/A' }}</td>
    <td>{{ $item->estimasi_durasi_hari ?? '-' }}</td>
    <td>
        @if ($item->status)
            <span class="badge bg-success">Aktif</span>
        @else
            <span class="badge bg-secondary">Tidak Aktif</span>
        @endif
    </td>
    {{-- Format tanggal --}}
    <td>{{ $item->created_at ? $item->created_at->format('d-m-Y H:i') : '-'}}</td>
    <td>{{ $item->updated_at ? $item->updated_at->format('d-m-Y H:i') : '-'}}</td>
    <td>
        {{-- Tombol Edit: data-layanan berisi JSON model Layanan --}}
        {{-- Kita perlu load relasi satuan agar masuk ke JSON --}}
        <button type="button" class="btn btn-warning btn-sm btn-edit"
                data-layanan="{{ json_encode($item->load('satuan')) }}"> {{-- load() relasi satuan --}}
            Edit
        </button>
        {{-- Tombol Hapus --}}
        <button type="button" class="btn btn-danger btn-sm btn-hapus">
            Hapus
        </button>
    </td>
</tr>
@empty
<tr>
    {{-- Sesuaikan colspan dengan jumlah kolom header --}}
    <td colspan="9" class="text-center">Belum ada data layanan.</td>
</tr>
@endforelse
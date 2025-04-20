{{-- resources/views/petugas/tbody.blade.php --}}
@php $no = 1; @endphp
@forelse ($assignments as $item)
    <tr>
        <td>{{ $no++ }}</td>
        {{-- Pastikan relasi 'user' dan 'cabang' ada sebelum diakses --}}
        <td>{{ $item->user->name ?? 'User tidak ditemukan' }}</td>
        <td>{{ $item->user->email ?? '-' }}</td>
        <td>{{ $item->cabang->nama_perusahaan ?? 'Cabang tidak ditemukan' }} ({{ $item->cabang->kode_cabang ?? '-' }})</td>
        <td>{{ $item->tugas ?: '-' }}</td>
        <td>{{ $item->created_at->format('d M Y H:i') }}</td>
        <td>
            <button class="btn btn-sm btn-warning btn-edit-petugas"
                    data-assignment="{{ htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') }}">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger btn-hapus-petugas"
                    data-id="{{ $item->id }}"
                    data-name="{{ $item->user->name ?? 'Petugas' }}"
                    data-assignment="{{ htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') }}">
                <i class="fas fa-trash"></i> Hapus
            </button>
        </td>
    </tr>
@empty
    <tr>
        {{-- Sesuaikan colspan jika jumlah kolom berubah --}}
        <td colspan="7" class="text-center">Tidak ada data penugasan ditemukan.</td>
    </tr>
@endforelse
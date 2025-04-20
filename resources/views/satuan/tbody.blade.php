{{-- File: resources/views/satuan/tbody.blade.php --}}
@forelse ($satuans as $item)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $item->nama_satuan }}</td>
    <td>{{ $item->deskripsi ?? '-' }}</td>
    <td>{{ $item->created_at->format('d-m-Y H:i') }}</td> {{-- Format tanggal --}}
    <td>{{ $item->updated_at->format('d-m-Y H:i') }}</td> {{-- Format tanggal --}}
    <td>
        {{-- Tombol Edit: data-satuan berisi JSON dari model Satuan --}}
        <button type="button" class="btn btn-warning btn-sm btn-edit"
                data-satuan="{{ json_encode($item) }}">
            Edit
        </button>
        {{-- Tombol Hapus: tidak perlu data karena kita ambil dari tombol edit di baris yg sama --}}
        <button type="button" class="btn btn-danger btn-sm btn-hapus">
            Hapus
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center">Belum ada data satuan.</td>
</tr>
@endforelse
{{-- File: resources/views/petugas/tbody.blade.php --}}
@forelse ($assignments as $assignment)
<tr>
    <td>{{ $loop->iteration }}</td>
    {{-- Akses data user dan cabang melalui relasi --}}
    <td>{{ $assignment->user->name ?? 'N/A' }}</td>
    <td>{{ $assignment->user->email ?? 'N/A' }}</td>
    <td>{{ $assignment->cabang->nama_perusahaan ?? 'N/A' }} ({{ $assignment->cabang->kode_cabang ?? 'N/A' }})</td>
    <td>{{ Str::limit($assignment->tugas, 50) ?? '-' }}</td>
    <td>{{ $assignment->created_at->format('d-m-Y H:i') }}</td>
    <td>
        <button class="btn btn-warning btn-sm btn-edit-petugas" title="Edit Penugasan"
                data-assignment="{{ json_encode($assignment) }}"> {{-- Kirim data assignment lengkap --}}
            <i class="fas fa-pencil-alt"></i>
        </button>
        <button class="btn btn-danger btn-sm btn-hapus-petugas" title="Hapus Penugasan"
                data-id="{{ $assignment->id }}"
                data-name="{{ $assignment->user->name ?? 'Petugas ini' }}"> {{-- Kirim nama untuk konfirmasi --}}
            <i class="fas fa-trash-alt"></i>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center">Tidak ada data penugasan petugas ditemukan.</td>
</tr>
@endforelse
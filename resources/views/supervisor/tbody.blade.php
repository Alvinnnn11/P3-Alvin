{{-- resources/views/Supervisor/tbody.blade.php --}}
@php $no = $no ?? 1; @endphp {{-- Inisialisasi nomor jika belum ada --}}
@forelse ($assignments as $assignment)
<tr>
    <td>{{ $no++ }}</td>
    <td>{{ $assignment->user->name ?? 'User Tidak Ditemukan' }}</td>
    <td>{{ $assignment->user->email ?? '-' }}</td>
    <td>{{ $assignment->cabang->nama_perusahaan ?? 'Cabang Tidak Ditemukan' }} ({{ $assignment->cabang->kode_cabang ?? 'N/A' }})</td>
    <td>{{ Str::limit($assignment->tugas, 50) }}</td> {{-- Batasi panjang tugas --}}
    <td>{{ $assignment->created_at->format('d M Y H:i') }}</td>
    <td>
        {{-- Encode assignment data as JSON directly in the attribute --}}
        <button class="btn btn-sm btn-warning btn-edit-supervisor"
                data-assignment="{{ htmlspecialchars(json_encode($assignment), ENT_QUOTES, 'UTF-8') }}"
                data-id="{{ $assignment->id }}">
             Edit
         </button>
         <button class="btn btn-sm btn-danger btn-hapus-supervisor"
                 data-id="{{ $assignment->id }}"
                 data-name="{{ $assignment->user->name ?? 'Supervisor' }}"
                 data-assignment="{{ htmlspecialchars(json_encode($assignment), ENT_QUOTES, 'UTF-8') }}">
             Hapus
         </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center">Tidak ada data penugasan supervisor ditemukan.</td>
</tr>
@endforelse
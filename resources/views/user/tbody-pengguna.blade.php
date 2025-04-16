{{-- File: resources/views/users/tbody-pengguna.blade.php --}}
@forelse ($penggunas as $pengguna) {{-- Gunakan variabel $penggunas --}}
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $pengguna->name }}</td>
    <td>{{ $pengguna->email }}</td>
    <td>{{ $pengguna->phone ?? '-' }}</td>
    <td>{{ Str::limit($pengguna->address, 40) ?? '-' }}</td>
    <td>
        @if($pengguna->foto_profile)
            <img src="{{ asset('storage/' . $pengguna->foto_profile) }}" width="40" height="40" style="object-fit: cover; border-radius: 50%;" alt="Foto {{ $pengguna->name }}">
        @else
             <span class="text-muted fst-italic">N/A</span>
        @endif
    </td>
    {{-- Level tidak perlu karena pasti pengguna --}}
    <td>
        @if($pengguna->status) <span class="badge bg-success">Aktif</span>
        @else <span class="badge bg-danger">Nonaktif</span>
        @endif
    </td>
    <td> {{-- Hanya Edit --}}
        <button class="btn btn-warning btn-sm btn-edit-user" {{-- Class bisa sama --}}
                title="Edit {{ $pengguna->name }}"
                data-user="{{ json_encode($pengguna) }}">
            <i class="fas fa-pencil-alt"></i>
        </button>
        {{-- Tombol Hapus dihilangkan --}}
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center">Tidak ada data pengguna (level pengguna) ditemukan.</td> {{-- Sesuaikan colspan --}}
</tr>
@endforelse
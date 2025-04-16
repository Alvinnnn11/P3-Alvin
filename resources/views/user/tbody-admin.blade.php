{{-- File: resources/views/users/tbody-admin.blade.php --}}
@forelse ($admins as $admin) {{-- Gunakan variabel $admins --}}
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $admin->name }}</td>
    <td>{{ $admin->email }}</td>
    <td>{{ $admin->phone ?? '-' }}</td>
    <td>{{ Str::limit($admin->address, 40) ?? '-' }}</td> {{-- Tambah Alamat --}}
    <td> {{-- Tambah Foto --}}
        @if($admin->foto_profile)
            <img src="{{ asset('storage/' . $admin->foto_profile) }}" width="40" height="40" style="object-fit: cover; border-radius: 50%;" alt="Foto {{ $admin->name }}">
        @else
             <span class="text-muted fst-italic">N/A</span>
        @endif
    </td>
    {{-- Level tidak perlu ditampilkan karena sudah pasti admin --}}
    {{-- <td><span class="badge bg-primary">{{ ucfirst($admin->level) }}</span></td> --}}
    <td> {{-- Tambah Status --}}
        @if($admin->status) <span class="badge bg-success">Aktif</span>
        @else <span class="badge bg-danger">Nonaktif</span>
        @endif
    </td>
    <td> {{-- Hanya Edit --}}
        <button class="btn btn-warning btn-sm btn-edit-user" {{-- Class tetap sama? Atau bedakan? Mari samakan dulu --}}
                title="Edit {{ $admin->name }}"
                data-user="{{ json_encode($admin) }}"> {{-- Kirim data user --}}
            <i class="fas fa-pencil-alt"></i>
        </button>
        {{-- Tombol Hapus dihilangkan --}}
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center">Tidak ada data admin ditemukan.</td> {{-- Sesuaikan colspan --}}
</tr>
@endforelse
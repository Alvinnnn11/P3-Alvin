{{-- File: resources/views/user/tbody.blade.php --}}
{{-- File ini HANYA berisi loop TR untuk dimasukkan ke dalam TBODY --}}

@forelse ($users as $key => $user)
<tr>
    <td>{{ $loop->iteration }}</td> {{-- Gunakan $loop->iteration untuk nomor urut --}}
    <td>{{ $user->name }}</td>
    <td>{{ $user->email }}</td>
    <td>{{ $user->phone ?? '-' }}</td> {{-- Tampilkan '-' jika null --}}
    <td>{{ $user->address ?? '-' }}</td> {{-- Tampilkan '-' jika null --}}
    <td>
        @if($user->foto_profile)
            {{-- Pastikan storage link sudah dibuat: php artisan storage:link --}}
            <img src="{{ asset('storage/' . $user->foto_profile) }}" width="40" height="40" style="object-fit: cover; border-radius: 50%;" alt="Foto {{ $user->name }}">
        @else
            {{-- Placeholder jika tidak ada foto --}}
             <span class="text-muted fst-italic">N/A</span>
            {{-- Atau gunakan ikon/avatar default --}}
            {{-- <i class="fas fa-user-circle fa-2x text-secondary"></i> --}}
        @endif
    </td>
    <td><span class="badge bg-secondary">{{ ucfirst($user->level) }}</span></td> {{-- Gunakan badge --}}
    <td>
        @if($user->status)
            <span class="badge bg-success">Aktif</span>
        @else
            <span class="badge bg-danger">Nonaktif</span>
        @endif
    </td>
    <td>
        {{-- Tombol Edit --}}
        <button class="btn btn-warning btn-sm btn-edit" title="Edit {{ $user->name }}"
                {{-- Encode data user ke JSON untuk atribut data-* --}}
                data-user="{{ json_encode($user) }}">
            <i class="fas fa-pencil-alt"></i> {{-- Contoh ikon --}}
        </button>

        {{-- Tombol Hapus --}}
        <button class="btn btn-danger btn-sm btn-hapus" title="Hapus {{ $user->name }}"
                data-id="{{ $user->id }}">
            <i class="fas fa-trash-alt"></i> {{-- Contoh ikon --}}
        </button>
    </td>
</tr>
@empty
{{-- Tampilkan pesan jika tidak ada data --}}
<tr>
    <td colspan="9" class="text-center">Tidak ada data pengguna ditemukan.</td>
</tr>
@endforelse
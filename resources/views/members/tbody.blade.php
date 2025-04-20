{{-- File: resources/views/members/tbody.blade.php --}}
@forelse ($members as $member) {{-- $members dikirim dari MemberController@index --}}
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $member->user->name ?? 'N/A' }}</td>
    <td>{{ $member->user->email ?? 'N/A' }}</td>
    <td>
        {{-- Tampilkan Status Keaktifan --}}
        @if($member->is_active)
            <span class="badge bg-success">Aktif</span>
        @else
            <span class="badge bg-warning text-dark">Belum Aktif</span> {{-- Status default setelah record dibuat tapi sebelum fee dibayar --}}
        @endif
    </td>
    <td class="text-end">Rp {{ number_format(Auth::user()->customer->saldo, 0, ',', '.') }}</td> {{-- Tampilkan Saldo --}}
    <td>{{ $member->joined_at ? $member->joined_at->format('d M Y H:i') : ($member->created_at ? $member->created_at->format('d M Y H:i') . ' (Pending)' : '-') }}</td> {{-- Tampilkan Tgl Aktif/Join --}}
    <td>
        {{-- Tombol Hapus Membership --}}
        <button class="btn btn-danger btn-sm btn-hapus-member" title="Hapus Membership {{ $member->user->name ?? '' }}"
                data-id="{{ $member->id }}" {{-- ID dari tabel members --}}
                data-name="{{ $member->user->name ?? 'User ini' }}">
            <i class="fas fa-user-minus"></i> Hapus Membership
        </button>
    </td>
</tr>
@empty
<tr>
    {{-- Sesuaikan colspan -> No, Nama, Email, Status, Saldo, Tgl Gabung, Aksi = 7 --}}
    <td colspan="7" class="text-center">Belum ada user yang terdaftar sebagai member.</td>
</tr>
@endforelse
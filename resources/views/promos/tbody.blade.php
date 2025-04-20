{{-- File: resources/views/promos/tbody.blade.php --}}
@forelse ($promos as $promo)
<tr data-promo-id="{{ $promo->id }}"> {{-- Tambahkan ID promo ke TR --}}
    <td>{{ $loop->iteration }}</td> {{-- Jika pakai pagination, nomor ini salah. Gunakan $loop->iteration + $promos->firstItem() - 1 di index view --}}
    <td>{{ $promo->nama_promo }}</td>
    <td>{{ $promo->cabang->nama_perusahaan ?? 'Semua Cabang' }}</td>
    <td>{!! $promo->khusus_member ? '<span class="badge bg-info">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>' !!}</td>
    <td>
        {{ number_format($promo->nilai_diskon, $promo->tipe_diskon == 'percentage' ? 1 : 0, ',', '.') }}
        {{ $promo->tipe_diskon == 'percentage' ? '%' : ' (Rp)' }}
    </td>
    <td class="text-end">{{ $promo->minimal_total_harga ? 'Rp '.number_format($promo->minimal_total_harga, 0, ',', '.') : '-' }}</td>
    <td>{{ $promo->tanggal_mulai->format('d M y H:i') }} - {{ $promo->tanggal_selesai->format('d M y H:i') }}</td>
    {{-- Kolom Sisa Waktu --}}
    <td class="sisa-waktu" data-end-date="{{ $promo->tanggal_selesai->toIso8601String() }}">
        <span class="placeholder col-8 placeholder-wave"></span> {{-- Placeholder awal --}}
    </td>
    <td>
        @if($promo->is_active) {{-- Gunakan accessor is_active --}}
            <span class="badge bg-success">Aktif</span>
        @else
             <span class="badge bg-danger">Tidak Aktif</span>
             {{-- <small class="d-block text-muted">({{ $promo->status_promo ? 'Kadaluarsa' : 'Manual Nonaktif' }})</small> --}}
        @endif
    </td>
    <td>
        {{-- Tombol Edit dengan kelas dan data-* yang benar --}}
        <button type="button" class="btn btn-warning btn-sm btn-edit-promo"
                data-promo="{{ json_encode($promo->load('cabang')) }}"> {{-- Sertakan relasi jika perlu di form --}}
            <i class="bx bx-edit-alt"></i> Edit
        </button>
        {{-- Tombol Hapus dengan kelas dan data-* yang benar --}}
        <button type="button" class="btn btn-danger btn-sm btn-hapus-promo"
                data-id="{{ $promo->id }}"
                data-name="{{ $promo->nama_promo }}"
                data-promo="{{ json_encode($promo) }}"> {{-- Data lengkap juga bisa berguna --}}
            <i class="bx bx-trash"></i> Hapus
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center">Belum ada data promo.</td> {{-- Sesuaikan colspan (10) --}}
</tr>
@endforelse
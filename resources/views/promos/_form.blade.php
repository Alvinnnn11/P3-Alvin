resources/views/promos/_form.blade.php
{{-- Partial ini membutuhkan variabel $promo (bisa null saat create) --}}
{{-- dan $cabangs (daftar cabang untuk admin) --}}
@php
    $promo = $promo ?? null; // Default null jika variabel $promo tidak dikirim (mode tambah)
    $isAdmin = Auth::user()->level === 'admin'; // Cek apakah user admin
@endphp

{{-- Hidden input untuk menangani checkbox 'khusus_member' jika tidak dicentang --}}
<input type="hidden" name="khusus_member" value="0">
{{-- Hidden input untuk status default jika radio tidak dipilih (seharusnya tidak terjadi jika required) --}}
{{-- <input type="hidden" name="status_promo" value="0"> --}}

<div class="row g-3">
    {{-- Kolom Kiri --}}
    <div class="col-md-6">
        <div class="mb-3">
            <label for="nama_promo" class="form-label">Nama Promo <span class="text-danger">*</span></label>
            <input type="text" name="nama_promo" id="nama_promo" class="form-control @error('nama_promo') is-invalid @enderror" value="{{ old('nama_promo', optional($promo)->nama_promo) }}" required>
            @error('nama_promo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3">{{ old('deskripsi', optional($promo)->deskripsi) }}</textarea>
            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

         <div class="mb-3">
            <label for="cabang_id" class="form-label">Target Cabang</label>
            <select name="cabang_id" id="cabang_id" class="form-select @error('cabang_id') is-invalid @enderror" {{ !$isAdmin ? 'disabled' : '' }}> {{-- Disable jika bukan admin --}}
                <option value="">-- Semua Cabang --</option>
                 {{-- $cabangs variabel berisi daftar cabang, dikirim dari controller --}}
                @foreach($cabangs as $cabang)
                 <option value="{{ $cabang->id }}" {{ old('cabang_id', optional($promo)->cabang_id) == $cabang->id ? 'selected' : '' }}>
                     {{ $cabang->nama_perusahaan }}
                 </option>
                @endforeach
            </select>
             @if(!$isAdmin && $assignedCabang = session('assigned_cabang'))
                <div class="form-text">Promo akan otomatis ditargetkan ke cabang Anda: {{ $assignedCabang->nama_perusahaan }}.</div>
                {{-- Jika SPV/Petugas, kirim ID cabangnya via hidden input jika diperlukan controller store/update --}}
                {{-- <input type="hidden" name="cabang_id" value="{{ $assignedCabang->id }}"> --}}
             @else
                 <div class="form-text">Kosongkan jika promo berlaku di semua cabang (hanya admin).</div>
             @endif
             @error('cabang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label d-block">Target Pengguna <span class="text-danger">*</span></label>
             <div class="form-check">
                 {{-- Checkbox: value 1 dikirim jika checked, jika tidak, hidden input value 0 yg terkirim --}}
                 <input class="form-check-input" type="checkbox" name="khusus_member" id="khusus_member" value="1" {{ old('khusus_member', optional($promo)->khusus_member ?? 0) == 1 ? 'checked' : '' }}>
                 <label class="form-check-label" for="khusus_member">
                     Hanya Berlaku Untuk Member?
                 </label>
             </div>
             @error('khusus_member') <div class="text-danger text-sm mt-1">{{ $message }}</div> @enderror
         </div>

         <div class="mb-3">
            <label for="minimal_total_harga" class="form-label">Minimal Total Belanja (Rp)</label>
            <input type="number" name="minimal_total_harga" id="minimal_total_harga" class="form-control @error('minimal_total_harga') is-invalid @enderror" value="{{ old('minimal_total_harga', optional($promo)->minimal_total_harga) }}" min="0" step="any">
            <div class="form-text">Kosongkan jika tidak ada minimal belanja.</div>
            @error('minimal_total_harga') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

    </div>

    {{-- Kolom Kanan --}}
    <div class="col-md-6">
        <div class="mb-3">
            <label for="tipe_diskon" class="form-label">Tipe Diskon <span class="text-danger">*</span></label>
            <select name="tipe_diskon" id="tipe_diskon" class="form-select @error('tipe_diskon') is-invalid @enderror" required>
                <option value="percentage" {{ old('tipe_diskon', optional($promo)->tipe_diskon) == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                <option value="fixed" {{ old('tipe_diskon', optional($promo)->tipe_diskon) == 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
            </select>
             @error('tipe_diskon') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

         <div class="mb-3">
            <label for="nilai_diskon" class="form-label">Nilai Diskon <span class="text-danger">*</span></label>
            <input type="number" name="nilai_diskon" id="nilai_diskon" class="form-control @error('nilai_diskon') is-invalid @enderror" value="{{ old('nilai_diskon', optional($promo)->nilai_diskon) }}" required min="0" step="any">
             <div class="form-text">Angka persen (misal 10) atau nominal (misal 5000).</div>
              @error('nilai_diskon') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', optional(optional($promo)->tanggal_mulai)->format('Y-m-d\TH:i')) }}" required>
             @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', optional(optional($promo)->tanggal_selesai)->format('Y-m-d\TH:i')) }}" required>
             @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

         <div class="mb-3">
             <label class="form-label d-block">Status Promo <span class="text-danger">*</span></label>
             <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status_promo" id="status_aktif" value="1" {{ old('status_promo', optional($promo)->status_promo ?? 1) == 1 ? 'checked' : '' }} required>
                <label class="form-check-label" for="status_aktif">Aktif</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status_promo" id="status_nonaktif" value="0" {{ old('status_promo', optional($promo)->status_promo) == 0 ? 'checked' : '' }} required>
                 <label class="form-check-label" for="status_nonaktif">Tidak Aktif</label>
            </div>
            @error('status_promo') <div class="text-danger text-sm mt-1">{{ $message }}</div> @enderror
         </div>
    </div>
</div>

{{-- Tombol Aksi Form (Disimpan di sini karena bagian dari form) --}}
<div class="pt-3 border-top mt-3 text-end">
     {{-- Tombol Batal ini akan dikontrol (show/hide) oleh JavaScript --}}
    <button type="button" class="btn btn-secondary" id="btnCancelPromo" style="display: none;">Batal</button>
    {{-- Tombol Simpan/Update --}}
    <button type="submit" class="btn btn-primary" id="btnSimpanPromo">
        {{-- Teks tombol akan diatur oleh JavaScript --}}
        Simpan Promo
    </button>
     {{-- Tombol Konfirmasi Hapus (jika pakai mode view delete di form) --}}
     {{-- <button type="button" class="btn btn-danger" id="btnConfirmDeletePromo" style="display: none;">Konfirmasi Hapus Promo</button> --}}
</div>
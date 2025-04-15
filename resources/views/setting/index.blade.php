@extends('layout.template')

@section('title', 'Pengaturan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <h4 class="fw-bold py-3">
        <i class="bx {{ $isPetugasView ? 'bx-buildings' : 'bx-cog' }}"></i> {{-- Ubah ikon untuk petugas --}}
        {{ $isPetugasView ? 'Pengaturan Cabang Bertugas' : 'Pengaturan Aplikasi Global' }}
    </h4>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
     @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">

        @if ($isPetugasView && $cabangInfo)
            {{-- ============================================== --}}
            {{-- FORM PENGATURAN CABANG UNTUK PETUGAS          --}}
            {{-- ============================================== --}}
            <h5 class="card-header bg-info text-white d-flex align-items-center">
                <i class="bx bx-edit me-2"></i> Edit Data Cabang: {{ $cabangInfo->nama_perusahaan }}
            </h5>
            <div class="card-body">
                 @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul>
                    </div>
                @endif

                 {{-- Form action ke route setting.update --}}
                 <form action="{{ route('setting.update') }}" method="post" enctype="multipart/form-data">
                     @csrf
                     @method('PUT')
                     <div class="row g-3">
                         {{-- Kolom Kiri --}}
                         <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Cabang</label>
                                {{-- Kode cabang tidak bisa diedit --}}
                                <input type="text" class="form-control" value="{{ $cabangInfo->kode_cabang }}" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="nama_perusahaan_cabang" class="form-label fw-bold">Nama Perusahaan/Cabang <span class="text-danger">*</span></label>
                                {{-- Nama input harus unik atau controller akan bingung, tapi kita bedakan berdasarkan level --}}
                                <input type="text" name="nama_perusahaan" id="nama_perusahaan_cabang" class="form-control" required value="{{ old('nama_perusahaan', $cabangInfo->nama_perusahaan) }}">
                                @error('nama_perusahaan') <div class="text-danger">{{$message}}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="alamat_perusahaan_cabang" class="form-label fw-bold">Alamat</label>
                                <textarea name="alamat_perusahaan" id="alamat_perusahaan_cabang" class="form-control" rows="3">{{ old('alamat_perusahaan', $cabangInfo->alamat_perusahaan) }}</textarea>
                                @error('alamat_perusahaan') <div class="text-danger">{{$message}}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="provinsi_perusahaan_cabang" class="form-label fw-bold">Provinsi</label>
                                <input type="text" name="provinsi_perusahaan" id="provinsi_perusahaan_cabang" class="form-control" value="{{ old('provinsi_perusahaan', $cabangInfo->provinsi_perusahaan) }}">
                                @error('provinsi_perusahaan') <div class="text-danger">{{$message}}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="kota_perusahaan_cabang" class="form-label fw-bold">Kota/Kabupaten</label>
                                <input type="text" name="kota_perusahaan" id="kota_perusahaan_cabang" class="form-control" value="{{ old('kota_perusahaan', $cabangInfo->kota_perusahaan) }}">
                                @error('kota_perusahaan') <div class="text-danger">{{$message}}</div> @enderror
                            </div>
                         </div>
                         {{-- Kolom Kanan --}}
                         <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kecamatan_perusahaan_cabang" class="form-label fw-bold">Kecamatan</label>
                                <input type="text" name="kecamatan_perusahaan" id="kecamatan_perusahaan_cabang" class="form-control" value="{{ old('kecamatan_perusahaan', $cabangInfo->kecamatan_perusahaan) }}">
                                @error('kecamatan_perusahaan') <div class="text-danger">{{$message}}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="kelurahan_perusahaan_cabang" class="form-label fw-bold">Kelurahan/Desa</label>
                                <input type="text" name="kelurahan_perusahaan" id="kelurahan_perusahaan_cabang" class="form-control" value="{{ old('kelurahan_perusahaan', $cabangInfo->kelurahan_perusahaan) }}">
                                @error('kelurahan_perusahaan') <div class="text-danger">{{$message}}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="kode_pos_cabang" class="form-label fw-bold">Kode Pos</label>
                                <input type="text" name="kode_pos" id="kode_pos_cabang" class="form-control" value="{{ old('kode_pos', $cabangInfo->kode_pos) }}">
                                @error('kode_pos') <div class="text-danger">{{$message}}</div> @enderror
                            </div>
                             {{-- Status mungkin tidak boleh diubah petugas --}}
                            {{-- <div class="mb-3">
                                <label for="status_cabang" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status_cabang" class="form-select" required>
                                    <option value="1" {{ old('status', $cabangInfo->status) == 1 ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('status', $cabangInfo->status) == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                                @error('status') <div class="text-danger">{{$message}}</div> @enderror
                            </div> --}}
                            <div class="mb-3">
                                <label for="logo_perusahaan_cabang" class="form-label fw-bold">Logo Cabang</label>
                                {{-- Nama input HARUS 'logo_perusahaan' sesuai validasi controller --}}
                                <input type="file" name="logo_perusahaan" id="logo_perusahaan_cabang" class="form-control" accept="image/*">
                                <div class="form-text">Kosongkan jika tidak ingin mengubah logo. Maks 2MB.</div>
                                @if($cabangInfo->logo_perusahaan)
                                    <small class="text-muted">Logo saat ini:</small>
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $cabangInfo->logo_perusahaan) }}" class="img-thumbnail" alt="Logo Cabang" width="120px" style="max-height: 100px; object-fit: contain;">
                                    </div>
                                @else
                                    <small class="text-muted">Belum ada logo.</small>
                                @endif
                                @error('logo_perusahaan') <div class="text-danger">{{$message}}</div> @enderror
                             </div>
                         </div>
                     </div>
                      <div class="text-end mt-4">
                         <button type="submit" class="btn btn-info"><i class="bx bx-save"></i> Simpan Perubahan Cabang</button>
                     </div>
                 </form>
            </div>
            {{-- ============================================== --}}
            {{-- AKHIR FORM PETUGAS                            --}}
            {{-- ============================================== --}}

        @elseif (!$isPetugasView && isset($setting))
            {{-- ================================================== --}}
            {{-- FORM PENGATURAN GLOBAL UNTUK ADMIN/SUPERVISOR    --}}
            {{-- ================================================== --}}
            <h5 class="card-header bg-primary text-white d-flex align-items-center">
                <i class="bx bx-edit me-2"></i> Form Pengaturan Global
            </h5>
            <div class="card-body">
                {{-- ... (Error handling @if($errors->any()) ... ) --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul> @foreach ($errors->all() as $error) <li><i class="bx bx-error-circle"></i> {{ $error }}</li> @endforeach </ul>
                    </div>
                @endif

                {{-- Form action ke route setting.update --}}
                <form action="{{ route('setting.update') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        {{-- Input untuk setting global (nama input: logo, nama_perusahaan, alamat, email, website) --}}
                        {{-- Contoh: --}}
                        <div class="col-md-6">
                            <label for="logo_global" class="form-label fw-bold"><i class="bx bx-image"></i> Logo Perusahaan (Global)</label>
                            {{-- Nama input HARUS 'logo' sesuai validasi controller --}}
                            <input type="file" name="logo" id="logo_global" class="form-control">
                            @if (!empty($setting->logo))
                                <small class="text-muted">Logo saat ini:</small>
                                <div class="mt-2"> <img src="{{ asset('storage/back/logo/' . $setting->logo) }}" class="img-thumbnail" alt="Logo Global" width="120px"> </div>
                            @else <small class="text-muted">Belum ada logo global.</small>
                            @endif
                            @error('logo') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6">
                            <label for="nama_perusahaan_global" class="form-label fw-bold"><i class="bx bx-building-house"></i> Nama Perusahaan (Global)</label>
                             {{-- Nama input HARUS 'nama_perusahaan' --}}
                            <input type="text" name="nama_perusahaan" id="nama_perusahaan_global" class="form-control" value="{{ old('nama_perusahaan', $setting->nama_perusahaan ?? '') }}">
                             @error('nama_perusahaan') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        {{-- ... (Input lain untuk alamat, email, website global) ... --}}
                        <div class="col-md-12">
                            <label for="alamat_global" class="form-label fw-bold"><i class="bx bx-map"></i> Alamat (Global)</label>
                            <textarea name="alamat" id="alamat_global" class="form-control" rows="3">{{ old('alamat', $setting->alamat ?? '') }}</textarea>
                            @error('alamat') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6">
                            <label for="email_global" class="form-label fw-bold"><i class="bx bx-envelope"></i> Email (Global)</label>
                            <input type="email" name="email" id="email_global" class="form-control" value="{{ old('email', $setting->email ?? '') }}">
                            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6">
                            <label for="website_global" class="form-label fw-bold"><i class="bx bx-link"></i> Website (Global)</label>
                            <input type="url" name="website" id="website_global" class="form-control" value="{{ old('website', $setting->website ?? '') }}">
                            @error('website') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan Pengaturan Global</button>
                    </div>
                </form>
            </div>
            {{-- ============================================== --}}
            {{-- AKHIR FORM ADMIN/SUPERVISOR                  --}}
            {{-- ============================================== --}}

        @else
            {{-- Tampilan jika data tidak ditemukan --}}
             <div class="card-body">
                <div class="alert alert-warning" role="alert">
                   <i class="bx bx-error-alt"></i> Informasi pengaturan atau data cabang tidak ditemukan untuk ditampilkan atau diedit.
                </div>
             </div>
        @endif

    </div>
</div>
@endsection
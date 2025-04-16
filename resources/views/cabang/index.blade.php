    @extends('layout.template') {{-- Sesuaikan nama layout --}}

    @section('title', 'Manajemen Cabang')

    @section('content')
        <!DOCTYPE html>
        <html>

        <head>
            <title>CRUD Cabang - Laravel</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <style>
                @media print {

                    #btnPrintReport,
                    .btn-edit,
                    .btn-delete,
                    #searchInput,
                    #merkForm {
                        display: none;
                    }
                }
            </style>
        </head>

        <body>
            <div class="container">
                <h4 class="my-3">Manajemen Cabang</h4>

                {{-- Card untuk Form Inline --}}
                <div class="card mb-4" id="cabangFormCard">
                    <div class="card-header">
                        <h5 class="card-title mb-0" id="formTitleCabang">Tambah Cabang Baru</h5>
                    </div>
                    <div class="card-body">
                        <form id="formCabang" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="formModeCabang" value="tambah">
                            <input type="hidden" id="cabangId">

                            <div id="error-messages-cabang" class="alert alert-danger" style="display: none;"></div>

                            <div class="row">
                                {{-- Kolom Kiri --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kode_cabang_display" class="form-label">Kode Cabang</label>
                                        <input type="text" id="kode_cabang_display" class="form-control" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nama_perusahaan" class="form-label">Nama Perusahaan/Cabang <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="nama_perusahaan" id="nama_perusahaan" class="form-control"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="alamat_perusahaan" class="form-label">Alamat</label>
                                        <textarea name="alamat_perusahaan" id="alamat_perusahaan" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="provinsi_perusahaan" class="form-label">Provinsi</label>
                                        <input type="text" name="provinsi_perusahaan" id="provinsi_perusahaan"
                                            class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="kota_perusahaan" class="form-label">Kota/Kabupaten</label>
                                        <input type="text" name="kota_perusahaan" id="kota_perusahaan" class="form-control">
                                    </div>

                                </div>
                                {{-- Kolom Kanan --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kecamatan_perusahaan" class="form-label">Kecamatan</label>
                                        <input type="text" name="kecamatan_perusahaan" id="kecamatan_perusahaan"
                                            class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="kelurahan_perusahaan" class="form-label">Kelurahan/Desa</label>
                                        <input type="text" name="kelurahan_perusahaan" id="kelurahan_perusahaan"
                                            class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="kode_pos" class="form-label">Kode Pos</label>
                                        <input type="text" name="kode_pos" id="kode_pos" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span
                                                class="text-danger">*</span></label>
                                        <select name="status" id="status" class="form-select" required>
                                            <option value="1">Aktif</option>
                                            <option value="0">Tidak Aktif</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="logo_perusahaan" class="form-label">Logo Perusahaan</label>
                                        <input type="file" name="logo_perusahaan" id="logo_perusahaan" class="form-control"
                                            accept="image/*">
                                        <div class="form-text">Kosongkan jika tidak ingin mengubah logo. Maks 2MB.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Preview Logo</label>
                                        <div id="logo-preview-container" class="mt-1">
                                            <img id="logo-preview" src="{{ asset('img/default-logo.png') }}"
                                                {{-- GANTI DENGAN PATH LOGO DEFAULT --}} alt="Logo Perusahaan"
                                                style="max-width: 150px; max-height: 100px; border-radius: 5px; object-fit: contain; border: 1px solid #ddd;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Aksi Form --}}
                            <div class="pt-3 border-top mt-3">
                                <button type="submit" class="btn btn-primary" id="btnSimpanCabang">Simpan Cabang
                                    Baru</button>
                                <button type="button" class="btn btn-danger" id="btnConfirmDeleteCabang"
                                    style="display: none;">Konfirmasi Hapus Cabang Ini</button>
                                <button type="button" class="btn btn-secondary" id="btnCancelCabang"
                                    style="display: none;">Batal / Kembali</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Card Tabel Cabang --}}
                <div class="card">
                    <div class="card-header">
                        Daftar Cabang
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>Kelurahan</th>
                                        <th>Kecamatan</th>
                                        <th>Kota</th>
                                        <th>Provinsi</th>
                                        <th>Kode Pos</th>
                                        <th>Logo</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="isiCabang">
                                    @forelse ($cabangs as $cabang)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $cabang->kode_cabang }}</td>
                                            <td>{{ $cabang->nama_perusahaan }}</td>
                                            <td>{{ Str::limit($cabang->alamat_perusahaan, 50) }}</td>
                                            <td>{{ $cabang->kelurahan_perusahaan ?? '-' }}</td> 
                                            <td>{{ $cabang->kecamatan_perusahaan ?? '-' }}</td>
                                            <td>{{ $cabang->kota_perusahaan ?? '-' }}</td> 
                                            <td>{{ $cabang->provinsi_perusahaan ?? '-' }}</td>
                                            <td>{{ $cabang->kode_pos ?? '-' }}</td>>
                                            <td>
                                                @if ($cabang->logo_perusahaan)
                                                    <img src="{{ asset('storage/' . $cabang->logo_perusahaan) }}"
                                                        width="80" height="40" style="object-fit: contain;"
                                                        alt="Logo {{ $cabang->nama_perusahaan }}">
                                                @else
                                                    <span class="text-muted fst-italic">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($cabang->status)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{-- Tombol Edit --}}
                                                <button class="btn btn-warning btn-sm btn-edit-cabang"
                                                    title="Edit {{ $cabang->nama_perusahaan }}"
                                                    data-cabang="{{ json_encode($cabang) }}">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                {{-- Tombol Hapus --}}
                                                <button class="btn btn-danger btn-sm btn-hapus-cabang"
                                                    title="Hapus {{ $cabang->nama_perusahaan }}"
                                                    data-id="{{ $cabang->id }}" data-cabang="{{ json_encode($cabang) }}">
                                                    {{-- Tambahkan data-cabang juga --}}
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Tidak ada data cabang ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endsection
        @push('js')
            <script>
                $(document).ready(function() {
                    console.log("Document Ready! Script Manajemen Cabang berjalan.");

                    // --- Konstanta & Variabel ---
                    const defaultLogo = "{{ asset('img/default-logo.png') }}"; // GANTI PATH LOGO DEFAULT
                    const storageBaseUrl = "{{ asset('storage') }}";
                    const formCabang = $('#formCabang');
                    const formCardCabang = $('#cabangFormCard');
                    const formTitleCabang = $('#formTitleCabang');
                    const cabangIdField = $('#cabangId');
                    const formModeCabangField = $('#formModeCabang');
                    const errorMessagesCabang = $('#error-messages-cabang');
                    const btnSimpanCabang = $('#btnSimpanCabang');
                    const btnCancelCabang = $('#btnCancelCabang');
                    const btnConfirmDeleteCabang = $('#btnConfirmDeleteCabang');
                    const logoPreviewImg = $('#logo-preview');
                    const inputLogoPerusahaan = $('#logo_perusahaan');

                    // Selector untuk input form cabang
                    const formInputsSelectorCabang = '#formCabang input, #formCabang select, #formCabang textarea';
                    const exceptionsSelectorCabang =
                        '[type=hidden], #btnCancelCabang, #btnConfirmDeleteCabang, #btnSimpanCabang';

                    // --- Fungsi Helper ---

                    function setFormStateCabang(mode = 'tambah', cabangData = null) {
                        formCabang[0].reset();
                        errorMessagesCabang.hide().html('');
                        formModeCabangField.val(mode);
                        cabangIdField.val(cabangData ? cabangData.id : '');

                        $(formInputsSelectorCabang).not(exceptionsSelectorCabang).prop('disabled', false);
                        inputLogoPerusahaan.val('');
                        logoPreviewImg.attr('src', defaultLogo);

                        if (mode === 'tambah') {
                            formTitleCabang.text('Tambah Cabang Baru');
                            btnSimpanCabang.text('Simpan Cabang Baru').show().prop('disabled', false);
                            btnCancelCabang.hide();
                            btnConfirmDeleteCabang.hide();
                            $('#kode_cabang_display').closest('.mb-3').hide();
                            // Mungkin perlu handle field khusus mode tambah jika ada
                        } else if (mode === 'edit') {
                            if (!cabangData) return;
                            formTitleCabang.text('Edit Cabang: ' + cabangData.nama_perusahaan);
                            populateFormCabang(cabangData);
                            btnSimpanCabang.text('Update Cabang').show().prop('disabled', false);
                            btnCancelCabang.show();
                            btnConfirmDeleteCabang.hide();
                        } else if (mode === 'delete') {
                            if (!cabangData) return;
                            formTitleCabang.text('Hapus Cabang (Konfirmasi): ' + cabangData.nama_perusahaan);
                            populateFormCabang(cabangData);
                            $(formInputsSelectorCabang).not(exceptionsSelectorCabang).prop('disabled', true);
                            $('#kode_cabang_display').prop('disabled', true).closest('.mb-3').show()
                            btnSimpanCabang.hide().prop('disabled', true);
                            btnCancelCabang.show();
                            btnConfirmDeleteCabang.show().prop('disabled', false);
                        }

                        $('html, body').animate({
                            scrollTop: formCardCabang.offset().top - 70
                        }, 300);
                    }

                    function populateFormCabang(cabang) {
                        $('#kode_cabang_display').val(cabang.kode_cabang);
                        $('#nama_perusahaan').val(cabang.nama_perusahaan);
                        $('#alamat_perusahaan').val(cabang.alamat_perusahaan);
                        $('#provinsi_perusahaan').val(cabang.provinsi_perusahaan);
                        $('#kota_perusahaan').val(cabang.kota_perusahaan);
                        $('#kecamatan_perusahaan').val(cabang.kecamatan_perusahaan);
                        $('#kelurahan_perusahaan').val(cabang.kelurahan_perusahaan);
                        $('#kode_pos').val(cabang.kode_pos);
                        $('#status').val(cabang.status ? '1' : '0');

                        if (cabang.logo_perusahaan) {
                            logoPreviewImg.attr('src', storageBaseUrl + '/' + cabang.logo_perusahaan);
                        } else {
                            logoPreviewImg.attr('src', defaultLogo);
                        }
                    }

                    function displayErrorsCabang(errors) {
                        let errorHtml = '<ul>';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul>';
                        errorMessagesCabang.html(errorHtml).show();
                    }

                    function refreshCabangTable() {
                        console.log("Attempting to refresh Cabang table...");
                        // Pastikan colspan=12
                        $('#isiCabang').html('<tr><td colspan="12" class="text-center">Memuat data...</td></tr>');
                        $.ajax({
                            url: "{{ route('cabang.data') }}",
                            type: 'GET',
                            success: function(data) {
                                console.log("AJAX success for refreshCabangTable. Data received:", data.length >
                                    0);
                                $('#isiCabang').html(data);
                            },
                            error: function(xhr) {
                                console.error("AJAX error in refreshCabangTable:", xhr);
                                // Pastikan colspan=12
                                $('#isiCabang').html(
                                    '<tr><td colspan="12" class="text-center text-danger">Gagal memuat data cabang.</td></tr>'
                                    );
                            }
                        });
                    }

                    // --- Event Listeners ---

                    // Tombol Edit Cabang di Tabel
                    $(document).on('click', '.btn-edit-cabang', function() {
                        console.log("Edit Cabang clicked");
                        let cabangJson = $(this).data('cabang');
                        let cabang = (typeof cabangJson === 'string') ? JSON.parse(cabangJson) : cabangJson;
                        setFormStateCabang('edit', cabang);
                    });

                    // Tombol Hapus Cabang di Tabel
                    $(document).on('click', '.btn-hapus-cabang', function() {
                        console.log("Delete Cabang view clicked");
                        let cabangJson = $(this).data('cabang'); // Ambil data dari tombol hapus
                        let cabang = (typeof cabangJson === 'string') ? JSON.parse(cabangJson) : cabangJson;
                        cabangIdField.val(cabang.id); // Set ID untuk tombol konfirmasi hapus
                        setFormStateCabang('delete', cabang);
                    });

                    // Tombol Batal Form Cabang
                    $('#btnCancelCabang').click(function() {
                        console.log("Cancel Cabang clicked");
                        setFormStateCabang('tambah');
                    });

                    // Submit Form Cabang (Tambah/Edit)
                    formCabang.submit(function(e) {
                        e.preventDefault();
                        btnSimpanCabang.prop('disabled', true).text('Menyimpan...');
                        errorMessagesCabang.hide().html('');

                        let mode = formModeCabangField.val();
                        let cabangId = cabangIdField.val();
                        let url = '';
                        let formData = new FormData(this);
                        let method = 'POST';

                        if (mode === 'tambah') {
                            url = "{{ route('cabang.store') }}";
                        } else if (mode === 'edit') {
                            let urlTemplate =
                            "{{ route('cabang.update', ['cabang' => ':id']) }}"; // Gunakan 'cabang' sesuai route model binding
                            url = urlTemplate.replace(':id', cabangId);
                            formData.append('_method', 'PUT');
                        } else {
                            btnSimpanCabang.prop('disabled', false); // Enable lagi jika mode aneh
                            return;
                        }

                        $.ajax({
                            url: url,
                            type: method,
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.success) {
                                    refreshCabangTable();
                                    setFormStateCabang('tambah');
                                    Swal.fire('Berhasil!', response.message, 'success');
                                } else {
                                    // Error dari sisi server (bukan validasi)
                                    Swal.fire('Gagal!', response.message || 'Terjadi kesalahan server.',
                                        'error');
                                    btnSimpanCabang.prop('disabled', false).text(mode === 'tambah' ?
                                        'Simpan Cabang Baru' : 'Update Cabang');
                                }
                            },
                            error: function(xhr) {
                                btnSimpanCabang.prop('disabled', false).text(mode === 'tambah' ?
                                    'Simpan Cabang Baru' : 'Update Cabang');
                                if (xhr.status === 422) {
                                    // Error validasi
                                    displayErrorsCabang(xhr.responseJSON.errors);
                                    Swal.fire('Error Validasi', 'Silakan periksa kembali isian form.',
                                        'error');
                                } else {
                                    console.error(xhr);
                                    Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || xhr
                                            .statusText || 'Tidak dapat terhubung ke server.'),
                                        'error');
                                }
                            }
                        });
                    });

                    // Tombol Konfirmasi Hapus Cabang
                    $('#btnConfirmDeleteCabang').click(function() {
                        let id = cabangIdField.val();
                        let namaCabang = $('#nama_perusahaan').val(); // Ambil nama dari form
                        if (!id) {
                            Swal.fire('Error', 'ID Cabang tidak ditemukan.', 'error');
                            return;
                        }
                        console.log("Confirm delete cabang clicked for ID:", id);

                        Swal.fire({
                            title: 'Anda Yakin?',
                            html: `Anda akan menghapus cabang: <strong>${namaCabang}</strong>.<br>Tindakan ini tidak bisa dibatalkan!`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Menghapus...',
                                    text: 'Mohon tunggu.',
                                    allowOutsideClick: false,
                                    didOpen: () => Swal.showLoading()
                                });

                                let urlTemplate =
                                "{{ route('cabang.destroy', ['cabang' => ':id']) }}"; // Gunakan 'cabang'
                                let url = urlTemplate.replace(':id', id);

                                $.ajax({
                                    url: url,
                                    type: 'POST',
                                    data: {
                                        _method: 'DELETE',
                                        _token: "{{ csrf_token() }}"
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            refreshCabangTable();
                                            setFormStateCabang('tambah');
                                            Swal.fire('Dihapus!', response.message, 'success');
                                        } else {
                                            Swal.fire('Gagal!', response.message ||
                                                'Terjadi kesalahan server.', 'error');
                                        }
                                    },
                                    error: function(xhr) {
                                        console.error(xhr);
                                        Swal.fire('Error ' + xhr.status, (xhr.responseJSON
                                                ?.message || xhr.statusText ||
                                                'Tidak dapat terhubung ke server.'),
                                            'error');
                                    }
                                    // complete: function() { // Tidak perlu disable lagi karena form direset }
                                });
                            }
                        });
                    });

                    // --- Inisialisasi ---
                    setFormStateCabang('tambah'); // Mulai dengan form tambah

                    // Preview logo saat file dipilih
                    inputLogoPerusahaan.change(function() {
                        const file = this.files[0];
                        if (file) {
                            let reader = new FileReader();
                            reader.onload = function(event) {
                                logoPreviewImg.attr('src', event.target.result);
                            }
                            reader.readAsDataURL(file);
                        } else {
                            // Jika pemilihan file dibatalkan, tampilkan logo lama (jika mode edit) atau default
                            let currentMode = formModeCabangField.val();
                            let currentId = cabangIdField.val();
                            if (currentMode === 'edit' && currentId) {
                                // Cari data cabang yg sedang diedit (perlu cara ambil data lagi atau simpan data awal)
                                // Untuk simpelnya, tampilkan default jika cancel
                                logoPreviewImg.attr('src', defaultLogo);
                            } else {
                                logoPreviewImg.attr('src', defaultLogo);
                            }
                        }
                    });

                });
            </script>
        </body>

        </html>
    @endpush

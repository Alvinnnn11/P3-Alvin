{{-- Meng-extend layout utama Anda (sesuaikan path) --}}
@extends('layout.template') {{-- Ganti 'layout.template' jika nama layout Anda berbeda --}}

@section('title', 'Manajemen Satuan') {{-- Judul Halaman --}}

@section('content') {{-- Sesuaikan nama section jika berbeda --}}
<!DOCTYPE html>
<html>
<head>
    {{-- Bootstrap & jQuery sudah di-include di layout utama? Jika tidak, uncomment di bawah --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Anda mungkin perlu memuat CSS & JS Bootstrap dari layout utama Anda --}}
</head>
<body>
    <div class="container-fluid"> {{-- Gunakan container-fluid agar lebih lebar jika perlu --}}
        <h4 class="py-3 mb-4">
            <span class="text-muted fw-light">Master Data /</span> Manajemen Satuan
        </h4>

        {{-- 1. Card untuk Form Inline --}}
        <div class="card mb-4" id="satuanFormCard">
            <div class="card-header">
                <h5 class="card-title mb-0" id="formTitle">Tambah Satuan Baru</h5>
            </div>
            <div class="card-body">
                <form id="formSatuan">
                    @csrf
                    <input type="hidden" id="formMode" value="tambah">
                    <input type="hidden" id="satuanId"> {{-- Ganti userId jadi satuanId --}}

                    {{-- Alert untuk menampilkan error validasi --}}
                    <div id="error-messages" class="alert alert-danger" style="display: none;"></div>

                    {{-- Baris untuk Input Fields --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_satuan" class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_satuan" id="nama_satuan" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <input type="text" name="deskripsi" id="deskripsi" class="form-control">
                        </div>
                    </div>

                    {{-- Tombol Aksi Form --}}
                    <div class="pt-3 border-top mt-1"> {{-- Kurangi margin top jika perlu --}}
                        <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan Satuan Baru</button>
                        {{-- Tombol konfirmasi hapus (muncul di mode delete) --}}
                        <button type="button" class="btn btn-danger" id="btnConfirmDelete" style="display: none;">Konfirmasi Hapus Satuan Ini</button>
                        {{-- Tombol Batal/Kembali ke Tambah (muncul di mode edit/delete) --}}
                        <button type="button" class="btn btn-secondary" id="btnCancel" style="display: none;">Batal / Kembali ke Tambah</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 2. Card Tabel Satuan --}}
        <div class="card">
            <div class="card-header">
                Daftar Satuan
                {{-- Tambahkan tombol refresh jika mau --}}
                {{-- <button class="btn btn-sm btn-secondary float-end" id="btnRefreshTable">Refresh</button> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-dark">
                            {{-- Header Tabel --}}
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Satuan</th>
                                <th scope="col">Deskripsi</th>
                                <th scope="col">Tgl Dibuat</th>
                                <th scope="col">Tgl Diperbarui</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="isiSatuan">
                            {{-- Data awal dimuat oleh view tbody --}}
                            @include('satuan.tbody', ['satuans' => $satuans])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
@endsection

@push('js') {{-- Sesuaikan nama stack jika berbeda --}}
<script>
    $(document).ready(function() {
        console.log("Document Ready! Script Manajemen Satuan (Inline Form) berjalan.");

        // --- Konstanta & Variabel ---
        const formSatuan = $('#formSatuan');
        const formCard = $('#satuanFormCard');
        const formTitle = $('#formTitle');
        const satuanIdField = $('#satuanId');
        const formModeField = $('#formMode');
        const errorMessages = $('#error-messages');
        const btnSimpan = $('#btnSimpan');
        const btnCancel = $('#btnCancel');
        const btnConfirmDelete = $('#btnConfirmDelete');

        // Target semua input/select/textarea di dalam form, kecuali yg dikecualikan
        const formInputsSelector = '#formSatuan input, #formSatuan select, #formSatuan textarea';
        const exceptionsSelector = '[type=hidden], #btnCancel, #btnConfirmDelete, #btnSimpan';

        // Fungsi untuk mengatur state form (tambah, edit, delete view)
        function setFormState(mode = 'tambah', satuanData = null) {
            formSatuan[0].reset();
            errorMessages.hide().html('');
            formModeField.val(mode);
            satuanIdField.val(satuanData ? satuanData.satuan_id : '');

            // Reset/Enable semua input dulu
            $(formInputsSelector).not(exceptionsSelector).prop('disabled', false);

            // Atur Tombol dan Judul berdasarkan Mode
            if (mode === 'tambah') {
                formTitle.text('Tambah Satuan Baru');
                btnSimpan.text('Simpan Satuan Baru').show().prop('disabled', false);
                btnCancel.hide();
                btnConfirmDelete.hide();
            } else if (mode === 'edit') {
                if (!satuanData) return;
                formTitle.text('Edit Satuan: ' + satuanData.nama_satuan);
                populateForm(satuanData); // Isi form dengan data
                btnSimpan.text('Update Satuan').show().prop('disabled', false);
                btnCancel.show();
                btnConfirmDelete.hide();
            } else if (mode === 'delete') {
                if (!satuanData) return;
                formTitle.text('Hapus Satuan (Konfirmasi): ' + satuanData.nama_satuan);
                populateForm(satuanData); // Isi form dengan data
                $(formInputsSelector).not(exceptionsSelector).prop('disabled', true); // Disable input
                btnSimpan.hide().prop('disabled', true);
                btnCancel.show();
                btnConfirmDelete.show().prop('disabled', false);
            }

            // Scroll ke form (opsional)
            $('html, body').animate({
                scrollTop: formCard.offset().top - 70 // Sesuaikan offset jika perlu
            }, 300);
        }

        // Fungsi untuk mengisi form dengan data satuan
        function populateForm(satuan) {
            $('#nama_satuan').val(satuan.nama_satuan);
            $('#deskripsi').val(satuan.deskripsi);
        }

        // Fungsi untuk menampilkan pesan error validasi
        function displayErrors(errors) {
            let errorHtml = '<ul>';
            $.each(errors, function(key, value) {
                errorHtml += '<li>' + value[0] + '</li>'; // Ambil pesan error pertama
            });
            errorHtml += '</ul>';
            errorMessages.html(errorHtml).show();
        }

        // Fungsi untuk refresh tabel satuan
        function refreshSatuanTable() {
            $('#isiSatuan').html('<tr><td colspan="6" class="text-center">Memuat data...</td></tr>');
            $.ajax({
                url: "{{ route('satuan.data') }}", // Route untuk mengambil data tbody
                type: 'GET',
                success: function(data) {
                    $('#isiSatuan').html(data); // Ganti isi tbody
                },
                error: function(xhr) {
                    console.error("Gagal memuat data tabel:", xhr);
                    $('#isiSatuan').html(
                        '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data. Silakan coba lagi.</td></tr>'
                    );
                }
            });
        }


        // --- Event Listeners ---

        // Tombol Edit di Tabel di Klik
        $(document).on('click', '.btn-edit', function() {
            let satuanJson = $(this).data('satuan');
            // Parsing JSON jika perlu (terkadang browser otomatis parse)
             let satuan = (typeof satuanJson === 'string') ? JSON.parse(satuanJson) : satuanJson;
             console.log("Edit clicked", satuan);
            setFormState('edit', satuan);
        });

        // Tombol Hapus di Tabel di Klik
        $(document).on('click', '.btn-hapus', function() {
            let satuanJson = $(this).closest('tr').find('.btn-edit').data('satuan'); // Ambil data dari tombol edit di baris yang sama
            let satuan = (typeof satuanJson === 'string') ? JSON.parse(satuanJson) : satuanJson;
            console.log("Delete view clicked", satuan);
             satuanIdField.val(satuan.satuan_id); // Set ID untuk tombol konfirmasi hapus
            setFormState('delete', satuan);
        });

        // Tombol Batal / Kembali ke Tambah di Klik
        $('#btnCancel').click(function() {
            console.log("Cancel clicked");
            setFormState('tambah'); // Kembali ke mode tambah
        });

        // Form Satuan di Submit (Handle Tambah & Edit)
        formSatuan.submit(function(e) {
            e.preventDefault();
            btnSimpan.prop('disabled', true).text('Menyimpan...');
            errorMessages.hide().html('');

            let mode = formModeField.val();
            let satuanId = satuanIdField.val();
            let url = '';
            let formData = new FormData(this); // Gunakan FormData
            let method = 'POST'; // Default method AJAX

            if (mode === 'tambah') {
                url = "{{ route('satuan.store') }}";
            } else if (mode === 'edit') {
                let urlTemplate = "{{ route('satuan.update', ['satuan' => ':id']) }}";
                url = urlTemplate.replace(':id', satuanId);
                formData.append('_method', 'PUT'); // Method spoofing for PUT
            } else {
                btnSimpan.prop('disabled', false).text('Simpan');
                return;
            }

            $.ajax({
                url: url,
                type: method, // Selalu POST karena pakai _method spoofing jika PUT
                data: formData,
                processData: false, // Penting untuk FormData
                contentType: false, // Penting untuk FormData
                success: function(response) {
                    if (response.success) {
                        refreshSatuanTable(); // Refresh tabel
                        setFormState('tambah'); // Kembali ke form tambah
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                         // Tampilkan pesan error spesifik dari server jika ada
                         let serverMessage = response.message || 'Terjadi kesalahan di server.';
                         Swal.fire('Gagal!', serverMessage, 'error');
                         btnSimpan.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Satuan Baru' : 'Update Satuan');
                    }
                },
                error: function(xhr) {
                    btnSimpan.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Satuan Baru' : 'Update Satuan');
                    if (xhr.status === 422) { // Error validasi
                        displayErrors(xhr.responseJSON.errors);
                         Swal.fire({
                            title: 'Error Validasi',
                             // Ambil HTML dari div error
                             html: $('#error-messages').html(),
                            icon: 'error'
                        });
                    } else {
                        console.error(xhr);
                        let errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                         Swal.fire('Error ' + xhr.status, errorMsg, 'error');
                    }
                }
            });
        });

        // Tombol Konfirmasi Hapus di Klik
        $('#btnConfirmDelete').click(function() {
            let id = satuanIdField.val();
            let namaSatuan = $('#nama_satuan').val(); // Ambil nama dari form yg disabled

            if (!id) {
                Swal.fire('Error', 'ID Satuan tidak ditemukan.', 'error');
                return;
            }
             console.log("Confirm delete clicked for ID:", id);

            // Konfirmasi SweetAlert2
            Swal.fire({
                title: 'Anda Yakin?',
                 html: `Anda akan menghapus satuan: <strong>${namaSatuan}</strong>.<br>Jika satuan ini digunakan oleh data Layanan, mungkin akan terjadi error atau tidak bisa dihapus (tergantung logika controller).<br>Tindakan ini tidak bisa dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("User confirmed deletion for ID:", id);
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    // AJAX Delete
                    let urlTemplate = "{{ route('satuan.destroy', ['satuan' => ':id']) }}";
                    let url = urlTemplate.replace(':id', id);

                    $.ajax({
                        url: url,
                        type: 'POST', // Tetap POST karena pakai method spoofing
                        data: {
                            _method: 'DELETE',
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                refreshSatuanTable();
                                setFormState('tambah');
                                Swal.fire('Dihapus!', response.message, 'success');
                            } else {
                                Swal.fire('Gagal!', response.message || 'Gagal menghapus data.', 'error');
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            let errorMsg = 'Tidak dapat terhubung ke server.';
                             if(xhr.responseJSON && xhr.responseJSON.message){
                                errorMsg = xhr.responseJSON.message;
                             } else if (xhr.statusText) {
                                errorMsg = xhr.statusText;
                             }
                            Swal.fire('Error ' + xhr.status, errorMsg, 'error');
                        }
                    });
                } else {
                    console.log("User cancelled deletion.");
                }
            });
        });

        // Tombol Refresh Tabel (Opsional)
        $('#btnRefreshTable').click(function() {
            refreshSatuanTable();
        });


        // --- Inisialisasi ---
        setFormState('tambah'); // Set form ke mode tambah saat halaman dimuat

    });
</script>
@endpush
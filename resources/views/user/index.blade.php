{{-- Meng-extend layout utama Anda --}}
@extends('layout.template')

@section('title', 'Manajemen Pengguna')

@section('content')
<!DOCTYPE html>
<html>
<head>
    <title>CRUD Merk Kendaraan - Laravel</title>
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
        <h4 class="my-3">Manajemen Pengguna</h4>

        {{-- 1. Hapus Tombol Tambah Awal (atau ubah fungsinya jadi "Reset/Batal") --}}
        {{-- <button class="btn btn-primary mb-3" id="btnTambah">...</button> --}}

        {{-- 2. Card untuk Form Inline (Gantikan Modal) --}}
        <div class="card mb-4" id="userFormCard">
            <div class="card-header">
                <h5 class="card-title mb-0" id="formTitle">Tambah Pengguna Baru</h5>
            </div>
            <div class="card-body">
        
                <form id="formUser" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="formMode" value="tambah">
                    <input type="hidden" id="userId">

                    {{-- Alert untuk menampilkan error validasi --}}
                    <div id="error-messages" class="alert alert-danger" style="display: none;"></div>

                    {{-- Baris untuk Input Fields (Gunakan Grid System Bootstrap misal col-md-6) --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span id="password-required"
                                    class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control"
                                aria-describedby="passwordHelp">
                            <div id="passwordHelp" class="form-text">Kosongkan jika tidak ingin mengubah password saat edit.
                                Minimal 7 karakter.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Telepon</label>
                            <input type="text" name="phone" id="phone" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3"> {{-- Alamat full width --}}
                            <label for="address" class="form-label">Alamat</label>
                            <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                            <select name="level" id="level" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="petugas">Petugas</option>
                                <option value="pengguna">Pengguna</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="foto_profile" class="form-label">Foto Profil</label>
                            <input type="file" name="foto_profile" id="foto_profile" class="form-control"
                                accept="image/png, image/jpeg, image/jpg">
                            <div id="fotoHelp" class="form-text">Kosongkan jika tidak ingin mengubah foto. Maksimal 2MB.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preview Foto</label>
                            <div id="foto-preview-container" class="mt-1">
                                <img id="foto-preview" src="{{ asset('path/to/default/avatar.png') }}" {{-- Ganti dg avatar default --}}
                                    alt="Foto Profil"
                                    style="max-width: 100px; max-height: 100px; border-radius: 5px; object-fit: cover;">
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi Form --}}
                    <div class="pt-3 border-top mt-3">
                        <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan Pengguna Baru</button>
                        {{-- Tombol konfirmasi hapus (muncul di mode delete) --}}
                        <button type="button" class="btn btn-danger" id="btnConfirmDelete"
                            style="display: none;">Konfirmasi Hapus Pengguna Ini</button>
                        {{-- Tombol Batal/Kembali ke Tambah (muncul di mode edit/delete) --}}
                        <button type="button" class="btn btn-secondary" id="btnCancel" style="display: none;">Batal /
                            Kembali ke Tambah</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 3. Card Tabel Pengguna (Tetap Sama) --}}
        <div class="card">
            <div class="card-header">
                Daftar Pengguna
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-dark">
                            {{-- Header Tabel --}}
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Email</th>
                                <th scope="col">Telepon</th>
                                <th scope="col">Alamat</th>
                                <th scope="col">Foto</th>
                                <th scope="col">Level</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="isiUser">
                            {{-- Data awal dimuat oleh view tbody --}}
                            @include('user.tbody', ['users' => $users])
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
            console.log("Document Ready! Script Manajemen Pengguna (Inline Form) berjalan.");

            // --- Konstanta & Variabel ---
            const defaultAvatar = "{{ asset('path/to/default/avatar.png') }}"; // Ganti dg path avatar default Anda
            const storageBaseUrl = "{{ asset('storage') }}";
            const formUser = $('#formUser');
            const formCard = $('#userFormCard'); // Cache elemen form card
            const formTitle = $('#formTitle');
            const userIdField = $('#userId');
            const formModeField = $('#formMode');
            const errorMessages = $('#error-messages');
            const btnSimpan = $('#btnSimpan');
            const btnCancel = $('#btnCancel');
            const btnConfirmDelete = $('#btnConfirmDelete');
            const passwordRequiredSpan = $('#password-required');
            const fotoPreviewContainer = $('#foto-preview-container');
            const fotoPreviewImg = $('#foto-preview');
            const inputFotoProfile = $('#foto_profile');

            // Target semua input/select/textarea di dalam form, kecuali yg dikecualikan
            const formInputsSelector = '#formUser input, #formUser select, #formUser textarea';
            const exceptionsSelector =
                '[type=hidden], #btnCancel, #btnConfirmDelete, #btnSimpan'; // Elemen yg tidak boleh disable

            

            function setFormState(mode = 'tambah', userData = null) {
                formUser[0].reset();
                errorMessages.hide().html('');
                formModeField.val(mode);
                userIdField.val(userData ? userData.id : '');

                // Reset/Enable semua input dulu
                $(formInputsSelector).not(exceptionsSelector).prop('disabled', false);
                inputFotoProfile.val('');
                fotoPreviewImg.attr('src', defaultAvatar);

                // Atur Tombol dan Judul berdasarkan Mode
                if (mode === 'tambah') {
                    formTitle.text('Tambah Pengguna Baru');
                    btnSimpan.text('Simpan Pengguna Baru').show().prop('disabled',
                        false); // <-- PASTIKAN ENABLED DI SINI
                    btnCancel.hide();
                    btnConfirmDelete.hide();
                    passwordRequiredSpan.show();
                } else if (mode === 'edit') {
                    if (!userData) return;
                    formTitle.text('Edit Pengguna: ' + userData.name);
                    populateForm(userData);
                    btnSimpan.text('Update Pengguna').show().prop('disabled',
                        false); // <-- PASTIKAN ENABLED DI SINI
                    btnCancel.show();
                    btnConfirmDelete.hide();
                    passwordRequiredSpan.hide();
                } else if (mode === 'delete') {
                    if (!userData) return;
                    formTitle.text('Hapus Pengguna (Konfirmasi): ' + userData.name);
                    populateForm(userData);
                    $(formInputsSelector).not(exceptionsSelector).prop('disabled', true);
                    btnSimpan.hide().prop('disabled', true); // <-- Sembunyikan dan disable btnSimpan
                    btnCancel.show();
                    btnConfirmDelete.show().prop('disabled', false); // <-- Tombol konfirmasi delete yg aktif
                    passwordRequiredSpan.hide();
                }

                // Scroll ke form (opsional)
                $('html, body').animate({
                    scrollTop: formCard.offset().top - 70
                }, 300);
            }
            // Fungsi untuk mengisi form dengan data user
            function populateForm(user) {
                $('#name').val(user.name);
                $('#email').val(user.email);
                $('#phone').val(user.phone);
                $('#address').val(user.address);
                $('#level').val(user.level);
                $('#status').val(user.status ? '1' : '0');
                // Password dikosongkan saat edit/delete view
                $('#password').val('');

                // Tampilkan preview foto jika ada
                if (user.foto_profile) {
                    fotoPreviewImg.attr('src', storageBaseUrl + '/' + user.foto_profile);
                } else {
                    fotoPreviewImg.attr('src', defaultAvatar);
                }
            }

            // Fungsi untuk menampilkan pesan error validasi
            function displayErrors(errors) {
                let errorHtml = '<ul>';
                $.each(errors, function(key, value) {
                    errorHtml += '<li>' + value[0] + '</li>';
                });
                errorHtml += '</ul>';
                errorMessages.html(errorHtml).show();
            }

            // Fungsi untuk refresh tabel
            function refreshUserTable() {
                $('#isiUser').html('<tr><td colspan="9" class="text-center">Memuat data...</td></tr>');
                $.ajax({
                    url: "{{ route('user.data') }}",
                    type: 'GET',
                    success: function(data) {
                        $('#isiUser').html(data);
                    },
                    error: function(xhr) {
                        console.error("Gagal memuat data tabel:", xhr);
                        $('#isiUser').html(
                            '<tr><td colspan="9" class="text-center text-danger">Gagal memuat data.</td></tr>'
                        );
                    }
                });
            }


            // --- Event Listeners ---

            // Tombol Edit di Tabel di Klik
            $(document).on('click', '.btn-edit', function() {
                console.log("Edit clicked");
                let userJson = $(this).data('user');
                let user = (typeof userJson === 'string') ? JSON.parse(userJson) : userJson;
                setFormState('edit', user);
            });

            // Tombol Hapus di Tabel di Klik
            $(document).on('click', '.btn-hapus', function() {
                console.log("Delete view clicked");
                let userJson = $(this).data('user'); // Ambil data lengkap dari tombol edit di baris yg sama
                // Jika tombol hapus tidak punya data-user, ambil dari tombol edit sebelahnya
                if (!userJson) {
                    userJson = $(this).siblings('.btn-edit').data('user');
                }
                let user = (typeof userJson === 'string') ? JSON.parse(userJson) : userJson;
                userIdField.val(user.id); // Set ID untuk tombol konfirmasi hapus
                setFormState('delete', user);
            });

            // Tombol Batal / Kembali ke Tambah di Klik
            $('#btnCancel').click(function() {
                console.log("Cancel clicked");
                setFormState('tambah'); // Kembali ke mode tambah
            });

            // Form User di Submit (Handle Tambah & Edit)
            formUser.submit(function(e) {
                e.preventDefault();
                btnSimpan.prop('disabled', true).text('Menyimpan...');
                errorMessages.hide().html('');

                let mode = formModeField.val();
                let userId = userIdField.val();
                let url = '';
                let formData = new FormData(this);
                let method = 'POST'; // Default method

                if (mode === 'tambah') {
                    url = "{{ route('user.store') }}";
                } else if (mode === 'edit') {
                    let urlTemplate =
                        "{{ route('user.update', ['user' => ':id']) }}"; // Ganti 'user' atau 'id' sesuai route Anda
                    url = urlTemplate.replace(':id', userId);
                    formData.append('_method', 'PUT'); // Method spoofing for PUT
                } else {
                    // Mode delete tidak dihandle oleh submit form ini
                    btnSimpan.prop('disabled', false).text('Simpan'); // Enable lagi jika mode aneh
                    return;
                }

                $.ajax({
                    url: url,
                    type: method, // Selalu POST karena pakai _method spoofing
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            refreshUserTable();
                            setFormState('tambah'); // Kembali ke mode tambah setelah sukses
                            Swal.fire({ // <-- Menjadi ini
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                timer: 1500, // Tutup otomatis setelah 1.5 detik
                                showConfirmButton: false // Sembunyikan tombol OK
                            }); // Ganti notifikasi
                        } else {
                            Swal.fire({ // <-- Menjadi ini
                                title: 'Gagal!',
                                text: (response.message ||
                                    'Terjadi kesalahan di server.'),
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        btnSimpan.prop('disabled', false).text(mode === 'tambah' ?
                            'Simpan Pengguna Baru' : 'Update Pengguna');
                        if (xhr.status === 422) {
                            displayErrors(xhr.responseJSON.errors);
                        } else {
                            console.error(xhr);
                            Swal.fire({
                                title: 'Error Validasi',
                                html: $('#error-messages')
                            .html(), // Ambil HTML error list
                                icon: 'error'
                            });
                        }
                    }
                });
            });

            // Tombol Konfirmasi Hapus di Klik
            $('#btnConfirmDelete').click(function() {
                let id = userIdField.val();
                let userName = $('#name').val(); // Ambil nama dari form yg disabled
                if (!id) {
                    Swal.fire('Error', 'ID Pengguna tidak ditemukan.', 'error');
                    return;
                }
                console.log("Confirm delete clicked for ID:", id);

                // Tampilkan Konfirmasi SweetAlert2
                Swal.fire({
                    title: 'Anda Yakin?',
                    html: `Anda akan menghapus pengguna: <strong>${userName}</strong>.<br>Tindakan ini tidak bisa dibatalkan!`, // Gunakan html untuk bold
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33', // Warna tombol konfirmasi (merah)
                    cancelButtonColor: '#3085d6', // Warna tombol batal
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    // Jika pengguna mengklik tombol "Ya, Hapus!"
                    if (result.isConfirmed) {
                        console.log("User confirmed deletion for ID:", id);
                        // Tampilkan loading state (opsional)
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Lanjutkan dengan AJAX Delete
                        let urlTemplate =
                            "{{ route('user.destroy', ['user' => ':id']) }}"; // Sesuaikan 'user'/'id'
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
                                    refreshUserTable();
                                    setFormState('tambah'); // Kembali ke mode tambah
                                    Swal.fire( // Notifikasi sukses
                                        'Dihapus!',
                                        response.message,
                                        'success'
                                    );
                                } else {
                                    Swal.fire( // Notifikasi gagal
                                        'Gagal!',
                                        (response.message ||
                                            'Terjadi kesalahan server.'),
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr);
                                Swal.fire( // Notifikasi error AJAX
                                    'Error ' + xhr.status,
                                    (xhr.responseJSON?.message || xhr.statusText ||
                                        'Tidak dapat terhubung ke server.'),
                                    'error'
                                );
                            },
                        });
                    } else {
                        // Jika pengguna klik Batal
                        console.log("User cancelled deletion.");
                    }
                }); // Akhir dari .then() SweetAlert
            });

            // --- Inisialisasi ---
            setFormState('tambah'); // Set form ke mode tambah saat halaman dimuat

        });
    </script>
</body>
</html>
@endpush

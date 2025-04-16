@extends('layout.template')

@section('title', 'Manajemen Admin')

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
    <h4 class="my-3">Manajemen Pengguna - Level Admin</h4>

    {{-- Card Form Inline --}}
    <div class="card mb-4" id="adminFormCard"> {{-- ID Card diubah --}}
        <div class="card-header">
             {{-- ID Judul diubah --}}
            <h5 class="card-title mb-0" id="formTitleAdmin">Tambah Admin Baru</h5>
        </div>
        <div class="card-body">
             {{-- ID Form diubah --}}
            <form id="formAdmin" enctype="multipart/form-data">
                @csrf
                 {{-- ID Mode diubah --}}
                <input type="hidden" id="formModeAdmin" value="tambah">
                 {{-- ID User tetap sama --}}
                <input type="hidden" id="userId">
                {{-- Hidden input untuk level --}}
                <input type="hidden" name="intended_level" value="admin">

                 {{-- ID Error Message diubah --}}
                <div id="error-messages-admin" class="alert alert-danger" style="display: none;"></div>

                <div class="row">
                     {{-- Kolom Kiri --}}
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" required>
                         {{-- ID Error Email diubah --}}
                        <div id="email-error-admin" class="invalid-feedback d-block" style="display: none;"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span id="password-required-admin" class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" aria-describedby="passwordHelpAdmin">
                        <div id="passwordHelpAdmin" class="form-text">Kosongkan jika tidak ingin mengubah. Min 7 karakter.</div>
                    </div>
                     <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Telepon</label>
                        <input type="text" name="phone" id="phone" class="form-control">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                    </div>
                     {{-- Kolom Kanan --}}
                     <div class="col-md-6 mb-3">
                        <label for="level" class="form-label">Level</label>
                        {{-- Level dibuat disabled dan valuenya 'admin' --}}
                        <select name="level" id="level" class="form-select" required disabled>
                            <option value="admin" selected>Admin</option>
                        </select>
                        <small class="text-muted">Level diatur otomatis ke Admin.</small>
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
                        <input type="file" name="foto_profile" id="foto_profile" class="form-control" accept="image/*">
                        <div class="form-text">Maks 2MB.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Preview Foto</label>
                         {{-- ID Preview diubah --}}
                        <div id="foto-preview-container-admin" class="mt-1">
                            <img id="foto-preview-admin" src="{{ asset('path/to/default/avatar.png') }}"
                                 alt="Foto Profil" style="max-width: 100px; max-height: 100px; border-radius: 5px; object-fit: cover;">
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="pt-3 border-top mt-3">
                     {{-- ID Tombol diubah --}}
                    <button type="submit" class="btn btn-primary" id="btnSimpanAdmin">Simpan Admin Baru</button>
                    {{-- Tombol Delete tidak ada --}}
                    <button type="button" class="btn btn-secondary" id="btnCancelAdmin" style="display: none;">Batal / Kembali</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Card Tabel Admin --}}
    <div class="card">
        <div class="card-header"> Daftar Admin </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Alamat</th> {{-- Tambah --}}
                            <th>Foto</th>   {{-- Tambah --}}
                            {{--<th>Level</th>--}} {{-- Hapus Level --}}
                            <th>Status</th> {{-- Tambah --}}
                            <th>Aksi</th>   {{-- Hanya Edit --}}
                        </tr>
                    </thead>
                     {{-- ID Body Tabel diubah --}}
                    <tbody id="isiAdmin">
                         {{-- Include partial tbody admin --}}
                        @include('user.tbody-admin', ['admins' => $admins])
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
// SALIN SEMUA KODE JAVASCRIPT DARI user.index.blade.php KE SINI
// KEMUDIAN LAKUKAN PENYESUAIAN BERIKUT:

$(document).ready(function() {
    console.log("Document Ready! Script Manajemen ADMIN berjalan.");

    // --- Konstanta & Variabel (Sesuaikan ID) ---
    const defaultAvatar = "{{ asset('path/to/default/avatar.png') }}";
    const storageBaseUrl = "{{ asset('storage') }}";
    const formAdmin = $('#formAdmin'); // Ganti ID
    const formCardAdmin = $('#adminFormCard'); // Ganti ID
    const formTitleAdmin = $('#formTitleAdmin'); // Ganti ID
    const userIdField = $('#userId'); // ID User tetap sama
    const formModeAdminField = $('#formModeAdmin'); // Ganti ID
    const errorMessagesAdmin = $('#error-messages-admin'); // Ganti ID
    const emailInput = $('#email'); // Tetap
    const emailErrorDiv = $('#email-error-admin'); // Ganti ID
    const btnSimpanAdmin = $('#btnSimpanAdmin'); // Ganti ID
    const btnCancelAdmin = $('#btnCancelAdmin'); // Ganti ID
    // const btnConfirmDelete = $('#btnConfirmDelete'); // Hapus jika tidak ada
    const passwordRequiredSpan = $('#password-required-admin'); // Ganti ID
    const fotoPreviewImg = $('#foto-preview-admin'); // Ganti ID
    const inputFotoProfile = $('#foto_profile'); // Tetap

    // Selector input (sesuaikan ID form)
    const formInputsSelector = '#formAdmin input, #formAdmin select, #formAdmin textarea';
    const exceptionsSelector = '[type=hidden], #btnCancelAdmin, #btnSimpanAdmin'; // Sesuaikan

    // --- Fungsi Helper (Sesuaikan ID dan Logika Level) ---

    function clearFieldErrors() {
        emailInput.removeClass('is-invalid');
        emailErrorDiv.hide().text('');
        errorMessagesAdmin.hide().html(''); // Target div error admin
    }

    function setFormStateAdmin(mode = 'tambah', userData = null) { // Ganti nama fungsi
        formAdmin[0].reset(); // Target form admin
        clearFieldErrors();
        formModeAdminField.val(mode); // Target mode admin
        userIdField.val(userData ? userData.id : '');
        inputFotoProfile.val('');
        fotoPreviewImg.attr('src', defaultAvatar);

        // Reset/Enable semua input
        $(formInputsSelector).not(exceptionsSelector).prop('disabled', false);
         // Atur level selalu 'admin' dan disabled
         $('#level').val('admin').prop('disabled', true);


        if (mode === 'tambah') {
            formTitleAdmin.text('Tambah Admin Baru'); // Ganti judul
            btnSimpanAdmin.text('Simpan Admin Baru').show().prop('disabled', false); // Ganti tombol
            btnCancelAdmin.hide(); // Ganti tombol
            passwordRequiredSpan.show();
        } else if (mode === 'edit') {
            if (!userData) return;
            formTitleAdmin.text('Edit Admin: ' + userData.name); // Ganti judul
            populateFormAdmin(userData); // Panggil fungsi populate baru
            btnSimpanAdmin.text('Update Admin').show().prop('disabled', false); // Ganti tombol
            btnCancelAdmin.show(); // Ganti tombol
            passwordRequiredSpan.hide();
            // Level sudah di-disable di atas
        }
        // Mode 'delete' tidak ada

        $('html, body').animate({ scrollTop: formCardAdmin.offset().top - 70 }, 300); // Target card admin
    }

    function populateFormAdmin(user) { // Ganti nama fungsi
        // Isi field seperti biasa, KECUALI level
        $('#name').val(user.name);
        $('#email').val(user.email);
        $('#phone').val(user.phone);
        $('#address').val(user.address);
        // $('#level').val('admin'); // Level sudah di set & disable di setFormStateAdmin
        $('#status').val(user.status ? '1' : '0');
        $('#password').val(''); // Kosongkan password

        // Handle foto preview
        if (user.foto_profile) {
            fotoPreviewImg.attr('src', storageBaseUrl + '/' + user.foto_profile);
        } else {
            fotoPreviewImg.attr('src', defaultAvatar);
        }
    }

    function displayErrorsAdmin(errors) { // Ganti nama fungsi
         clearFieldErrors();
         let generalErrorsHtml = '<ul>';
         let emailErrorMsg = null;
         $.each(errors, function(key, value) {
             let messages = value.join('<br>');
             if (key === 'email') {
                 emailErrorMsg = messages;
                 emailInput.addClass('is-invalid');
                 emailErrorDiv.html(messages).show(); // Target div error admin
             } else {
                 generalErrorsHtml += `<li><strong>${key}:</strong> ${messages}</li>`;
             }
         });
         generalErrorsHtml += '</ul>';
         if (generalErrorsHtml !== '<ul></ul>') {
              errorMessagesAdmin.html(generalErrorsHtml).show(); // Target div error admin
         } else if(!emailErrorMsg) {
              errorMessagesAdmin.html('<ul><li>Terjadi kesalahan validasi.</li></ul>').show();
         }
     }

    function refreshAdminTable() { // Ganti nama fungsi & route
         console.log("Refreshing Admin table...");
         // Sesuaikan colspan jadi 8 (No, Nama, Email, Telp, Alamat, Foto, Status, Aksi)
         $('#isiAdmin').html('<tr><td colspan="8" class="text-center">Memuat data...</td></tr>'); // Target tbody admin
         $.ajax({
             url: "{{ route('admin.data') }}", // <-- Route data admin baru
             type: 'GET',
             success: function(data) {
                 console.log("Admin data received for refresh.");
                 $('#isiAdmin').html(data); // Update tbody admin
             },
             error: function(xhr) {
                 console.error("Gagal memuat data tabel admin:", xhr);
                 $('#isiAdmin').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data admin.</td></tr>');
             }
         });
     }

    // --- Event Listeners (Sesuaikan selector & fungsi) ---

     // Tombol Edit di Tabel (gunakan class .btn-edit-user seperti di tbody-admin)
     $(document).on('click', '.btn-edit-user', function() { // Tetap pakai class ini?
         console.log("Edit Admin clicked");
         let userJson = $(this).data('user');
         let user = (typeof userJson === 'string') ? JSON.parse(userJson) : userJson;
         setFormStateAdmin('edit', user); // Panggil fungsi state admin
     });

     // Tombol Hapus tidak ada listenernya

     // Tombol Batal Form Admin
     $('#btnCancelAdmin').click(function() { // Target tombol cancel admin
         console.log("Cancel Admin clicked");
         setFormStateAdmin('tambah'); // Panggil fungsi state admin
     });

      // Submit Form Admin (Tambah/Edit)
      formAdmin.submit(function(e) { // Target form admin
          e.preventDefault();
          btnSimpanAdmin.prop('disabled', true).text('Menyimpan...'); // Target tombol simpan admin
          clearFieldErrors();

          let mode = formModeAdminField.val(); // Target mode admin
          let userId = userIdField.val();
          let url = '';
          let formData = new FormData(this); // Form ini sudah punya hidden input intended_level=admin
          let method = 'POST';

          // URL tetap ke user.store dan user.update
          if (mode === 'tambah') {
              url = "{{ route('user.store') }}";
          } else if (mode === 'edit') {
              let urlTemplate = "{{ route('user.update', ['user' => ':id']) }}";
              url = urlTemplate.replace(':id', userId);
              formData.append('_method', 'PUT');
               // Hapus 'level' dari formData karena inputnya disabled dan tidak boleh diupdate
              formData.delete('level');
          } else {
               btnSimpanAdmin.prop('disabled', false).text('Simpan'); return;
          }

          $.ajax({
              url: url, type: method, data: formData, processData: false, contentType: false,
              success: function (response) {
                  if (response.success) {
                      refreshAdminTable(); // Panggil refresh admin
                      setFormStateAdmin('tambah'); // Panggil state admin
                      Swal.fire({
                         title: 'Berhasil!',
                         text: response.message, // Pesan dari controller
                         icon: 'success',
                         timer: 1800, // Tampil 1.8 detik
                         showConfirmButton: false // Sembunyikan tombol OK
                     });
                  } else {
                    Swal.fire({
                         title: 'Gagal!',
                         text: response.message || 'Terjadi kesalahan saat menyimpan data.',
                         icon: 'error'
                      });
                      btnSimpanAdmin.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Admin Baru' : 'Update Admin');
                  }
              },
              error: function (xhr) {
                  btnSimpanAdmin.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Admin Baru' : 'Update Admin');
                  if (xhr.status === 422) {
                      displayErrorsAdmin(xhr.responseJSON.errors); // Panggil display error admin
                      Swal.fire({
                         title: 'Error Validasi',
                         html: 'Terdapat kesalahan pada input Anda:<br><ul class="text-start">' + Object.values(xhr.responseJSON.errors).map(err => `<li>${err[0]}</li>`).join('') + '</ul>', // Tampilkan list error
                         icon: 'error'
                      });
                  } else {
                      console.error(xhr);
                      Swal.fire({
                         title: 'Error ' + xhr.status,
                         text: (xhr.responseJSON?.message || xhr.statusText || 'Tidak dapat terhubung ke server.'),
                         icon: 'error'
                      });
                  }
              }
          });
      });

       // Listener untuk preview foto (sesuaikan ID preview jika perlu)
       inputFotoProfile.change(function(){
            const file = this.files[0];
            if (file){
                let reader = new FileReader();
                reader.onload = function(event){
                    fotoPreviewImg.attr('src', event.target.result); // Target ID preview admin
                }
                reader.readAsDataURL(file);
            } else {
                 fotoPreviewImg.attr('src', defaultAvatar); // Reset ke default
            }
        });

    // --- Inisialisasi ---
    setFormStateAdmin('tambah'); // Panggil state admin

});
</script>
</body>
</html>
@endpush
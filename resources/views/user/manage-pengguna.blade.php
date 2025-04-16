@extends('layout.template')

@section('title', 'Manajemen Pengguna Biasa')

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
    <h4 class="my-3">Manajemen Pengguna - Level Pengguna</h4>

    {{-- Card Form Inline --}}
    <div class="card mb-4" id="penggunaFormCard"> {{-- ID Card diubah --}}
        <div class="card-header">
            <h5 class="card-title mb-0" id="formTitlePengguna">Tambah Pengguna Baru</h5> {{-- ID Judul diubah --}}
        </div>
        <div class="card-body">
            <form id="formPengguna" enctype="multipart/form-data"> {{-- ID Form diubah --}}
                @csrf
                <input type="hidden" id="formModePengguna" value="tambah"> {{-- ID Mode diubah --}}
                <input type="hidden" id="userId"> {{-- ID User tetap sama --}}
                <input type="hidden" name="intended_level" value="pengguna"> {{-- Value: pengguna --}}

                <div id="error-messages-pengguna" class="alert alert-danger" style="display: none;"></div> {{-- ID Error Message diubah --}}

                <div class="row">
                    {{-- Kolom Kiri --}}
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" required>
                        <div id="email-error-pengguna" class="invalid-feedback d-block" style="display: none;"></div> {{-- ID Error Email diubah --}}
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span id="password-required-pengguna" class="text-danger">*</span></label> {{-- ID Span diubah --}}
                        <input type="password" name="password" id="password" class="form-control" aria-describedby="passwordHelpPengguna"> {{-- aria-describedby diubah --}}
                        <div id="passwordHelpPengguna" class="form-text">Kosongkan jika tidak ingin mengubah. Min 7 karakter.</div> {{-- ID Help diubah --}}
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
                        <select name="level" id="level" class="form-select" required disabled>
                            <option value="pengguna" selected>Pengguna</option> {{-- Value: pengguna --}}
                        </select>
                        <small class="text-muted">Level diatur otomatis ke Pengguna.</small>
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
                        <div id="foto-preview-container-pengguna" class="mt-1"> {{-- ID Preview diubah --}}
                            <img id="foto-preview-pengguna" src="{{ asset('path/to/default/avatar.png') }}" {{-- Ganti path default --}}
                                 alt="Foto Profil" style="max-width: 100px; max-height: 100px; border-radius: 5px; object-fit: cover;">
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="pt-3 border-top mt-3">
                    <button type="submit" class="btn btn-primary" id="btnSimpanPengguna">Simpan Pengguna Baru</button> {{-- ID Tombol diubah --}}
                    <button type="button" class="btn btn-secondary" id="btnCancelPengguna" style="display: none;">Batal / Kembali</button> {{-- ID Tombol diubah --}}
                </div>
            </form>
        </div>
    </div>

    {{-- Card Tabel Pengguna --}}
    <div class="card">
        <div class="card-header"> Daftar Pengguna (Level Pengguna) </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Foto</th>
                            <th>Status</th>
                            <th>Aksi</th> {{-- Hanya Edit --}}
                        </tr>
                    </thead>
                    <tbody id="isiPengguna"> {{-- ID Body Tabel diubah --}}
                        @include('user.tbody-pengguna', ['penggunas' => $penggunas]) {{-- Include partial pengguna --}}
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
    console.log("Document Ready! Script Manajemen PENGGUNA berjalan.");

    // --- Konstanta & Variabel (Sesuaikan ID) ---
    const defaultAvatar = "{{ asset('path/to/default/avatar.png') }}";
    const storageBaseUrl = "{{ asset('storage') }}";
    const formPengguna = $('#formPengguna'); // Ganti ID
    const formCardPengguna = $('#penggunaFormCard'); // Ganti ID
    const formTitlePengguna = $('#formTitlePengguna'); // Ganti ID
    const userIdField = $('#userId');
    const formModePenggunaField = $('#formModePengguna'); // Ganti ID
    const errorMessagesPengguna = $('#error-messages-pengguna'); // Ganti ID
    const emailInput = $('#email');
    const emailErrorDiv = $('#email-error-pengguna'); // Ganti ID
    const btnSimpanPengguna = $('#btnSimpanPengguna'); // Ganti ID
    const btnCancelPengguna = $('#btnCancelPengguna'); // Ganti ID
    const passwordRequiredSpan = $('#password-required-pengguna'); // Ganti ID
    const fotoPreviewImg = $('#foto-preview-pengguna'); // Ganti ID
    const inputFotoProfile = $('#foto_profile');

    const formInputsSelector = '#formPengguna input, #formPengguna select, #formPengguna textarea';
    const exceptionsSelector = '[type=hidden], #btnCancelPengguna, #btnSimpanPengguna'; // Sesuaikan

    // --- Fungsi Helper ---

    function clearFieldErrors() {
        emailInput.removeClass('is-invalid');
        emailErrorDiv.hide().text('');
        errorMessagesPengguna.hide().html(''); // Target div error pengguna
    }

    // Ganti nama fungsi dan logika internal
    function setFormStatePengguna(mode = 'tambah', userData = null) {
        formPengguna[0].reset();
        clearFieldErrors();
        formModePenggunaField.val(mode);
        userIdField.val(userData ? userData.id : '');
        inputFotoProfile.val('');
        fotoPreviewImg.attr('src', defaultAvatar);

        $(formInputsSelector).not(exceptionsSelector).prop('disabled', false);
        // Atur level selalu 'pengguna' dan disabled
        $('#level').val('pengguna').prop('disabled', true); // <-- Value: pengguna

        if (mode === 'tambah') {
            formTitlePengguna.text('Tambah Pengguna Baru'); // Ganti judul
            btnSimpanPengguna.text('Simpan Pengguna Baru').show().prop('disabled', false); // Ganti tombol
            btnCancelPengguna.hide(); // Ganti tombol
            passwordRequiredSpan.show();
        } else if (mode === 'edit') {
            if (!userData) return;
            formTitlePengguna.text('Edit Pengguna: ' + userData.name); // Ganti judul
            populateFormPengguna(userData); // Panggil fungsi populate baru
            btnSimpanPengguna.text('Update Pengguna').show().prop('disabled', false); // Ganti tombol
            btnCancelPengguna.show(); // Ganti tombol
            passwordRequiredSpan.hide();
        }
        // Mode delete tidak ada

        $('html, body').animate({ scrollTop: formCardPengguna.offset().top - 70 }, 300); // Target card pengguna
    }

    // Ganti nama fungsi
    function populateFormPengguna(user) {
        $('#name').val(user.name);
        $('#email').val(user.email);
        $('#phone').val(user.phone);
        $('#address').val(user.address);
        // $('#level').val('pengguna'); // Sudah di setFormStatePengguna
        $('#status').val(user.status ? '1' : '0');
        $('#password').val('');

        if (user.foto_profile) {
            fotoPreviewImg.attr('src', storageBaseUrl + '/' + user.foto_profile);
        } else {
            fotoPreviewImg.attr('src', defaultAvatar);
        }
    }

    // Ganti nama fungsi
    function displayErrorsPengguna(errors) {
         clearFieldErrors();
         let generalErrorsHtml = '<ul>';
         let emailErrorMsg = null;
         $.each(errors, function(key, value) {
             let messages = value.join('<br>');
             if (key === 'email') {
                 emailErrorMsg = messages;
                 emailInput.addClass('is-invalid');
                 emailErrorDiv.html(messages).show(); // Target div error pengguna
             } else {
                 generalErrorsHtml += `<li><strong>${key}:</strong> ${messages}</li>`;
             }
         });
         generalErrorsHtml += '</ul>';
         if (generalErrorsHtml !== '<ul></ul>') {
              errorMessagesPengguna.html(generalErrorsHtml).show(); // Target div error pengguna
         } else if(!emailErrorMsg) {
              errorMessagesPengguna.html('<ul><li>Terjadi kesalahan validasi.</li></ul>').show();
         }
     }

    // Ganti nama fungsi & route & target tbody
    function refreshPenggunaTable() {
         console.log("Refreshing Pengguna table...");
         // Sesuaikan colspan jadi 8
         $('#isiPengguna').html('<tr><td colspan="8" class="text-center">Memuat data...</td></tr>'); // Target tbody pengguna
         $.ajax({
             url: "{{ route('pengguna.data') }}", // <-- Route data pengguna baru
             type: 'GET',
             success: function(data) {
                 console.log("Pengguna data received for refresh.");
                 $('#isiPengguna').html(data); // Update tbody pengguna
             },
             error: function(xhr) {
                 console.error("Gagal memuat data tabel pengguna:", xhr);
                 $('#isiPengguna').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data pengguna.</td></tr>');
             }
         });
     }

    // --- Event Listeners ---

     // Tombol Edit di Tabel
     $(document).on('click', '.btn-edit-user', function() { // Class tombol edit bisa sama
         console.log("Edit Pengguna clicked");
         let userJson = $(this).data('user');
         let user = (typeof userJson === 'string') ? JSON.parse(userJson) : userJson;
         setFormStatePengguna('edit', user); // Panggil fungsi state pengguna
     });

     // Tombol Hapus tidak ada

     // Tombol Batal Form Pengguna
     $('#btnCancelPengguna').click(function() { // Target tombol cancel pengguna
         console.log("Cancel Pengguna clicked");
         setFormStatePengguna('tambah'); // Panggil fungsi state pengguna
     });

      // Submit Form Pengguna (Tambah/Edit)
      formPengguna.submit(function(e) { // Target form pengguna
          e.preventDefault();
          btnSimpanPengguna.prop('disabled', true).text('Menyimpan...'); // Target tombol simpan pengguna
          clearFieldErrors();

          let mode = formModePenggunaField.val(); // Target mode pengguna
          let userId = userIdField.val();
          let url = '';
          let formData = new FormData(this); // Form ini punya hidden intended_level=pengguna
          let method = 'POST';

          // URL tetap ke user.store dan user.update
          if (mode === 'tambah') {
              url = "{{ route('user.store') }}";
              // intended_level=pengguna sudah ada di form
          } else if (mode === 'edit') {
              let urlTemplate = "{{ route('user.update', ['user' => ':id']) }}"; // Sesuaikan user/id
              url = urlTemplate.replace(':id', userId);
              formData.append('_method', 'PUT');
              formData.delete('level'); // Hapus level karena disabled
          } else {
               btnSimpanPengguna.prop('disabled', false).text('Simpan'); return;
          }

          $.ajax({
              url: url, type: method, data: formData, processData: false, contentType: false,
              success: function (response) {
                  if (response.success) {
                      refreshPenggunaTable(); // Panggil refresh pengguna
                      setFormStatePengguna('tambah'); // Panggil state pengguna
                      Swal.fire('Berhasil!', response.message, 'success');
                  } else {
                      Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                      btnSimpanPengguna.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Pengguna Baru' : 'Update Pengguna'); // Target tombol pengguna
                  }
              },
              error: function (xhr) {
                  btnSimpanPengguna.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Pengguna Baru' : 'Update Pengguna'); // Target tombol pengguna
                  if (xhr.status === 422) {
                      displayErrorsPengguna(xhr.responseJSON.errors); // Panggil display error pengguna
                      Swal.fire('Error Validasi', 'Periksa isian form.', 'error');
                  } else {
                      console.error(xhr);
                      Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Terjadi kesalahan.'), 'error');
                  }
              }
          });
      });

       // Listener untuk preview foto
       inputFotoProfile.change(function(){ // Target input foto yg sama
            const file = this.files[0];
            if (file){
                let reader = new FileReader();
                reader.onload = function(event){
                    fotoPreviewImg.attr('src', event.target.result); // Target ID preview pengguna
                }
                reader.readAsDataURL(file);
            } else {
                 fotoPreviewImg.attr('src', defaultAvatar); // Reset ke default
            }
        });

    // --- Inisialisasi ---
    setFormStatePengguna('tambah'); // Panggil state pengguna

});
</script>
</body>
</html>
@endpush
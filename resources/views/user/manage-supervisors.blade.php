@extends('layout.template')

@section('title', 'Manajemen supervisor')

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
    <h4 class="my-3">Manajemen Pengguna - Level supervisor</h4>

    {{-- Card Form Inline --}}
    <div class="card mb-4" id="supervisorFormCard"> {{-- ID Card diubah --}}
        <div class="card-header">
             {{-- ID Judul diubah --}}
            <h5 class="card-title mb-0" id="formTitlesupervisor">Tambah supervisor Baru</h5>
        </div>
        <div class="card-body">
             {{-- ID Form diubah --}}
            <form id="formsupervisor" enctype="multipart/form-data">
                @csrf
                 {{-- ID Mode diubah --}}
                <input type="hidden" id="formModesupervisor" value="tambah">
                 {{-- ID User tetap sama --}}
                <input type="hidden" id="userId">
                {{-- Hidden input untuk level --}}
                <input type="hidden" name="intended_level" value="supervisor">

                 {{-- ID Error Message diubah --}}
                <div id="error-messages-supervisor" class="alert alert-danger" style="display: none;"></div>

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
                        <div id="email-error-supervisor" class="invalid-feedback d-block" style="display: none;"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span id="password-required-supervisor" class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" aria-describedby="passwordHelpsupervisor">
                        <div id="passwordHelpsupervisor" class="form-text">Kosongkan jika tidak ingin mengubah. Min 7 karakter.</div>
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
                        {{-- Level dibuat disabled dan valuenya 'supervisor' --}}
                        <select name="level" id="level" class="form-select" required disabled>
                            <option value="supervisor" selected>supervisor</option>
                        </select>
                        <small class="text-muted">Level diatur otomatis ke supervisor.</small>
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
                        <div id="foto-preview-container-supervisor" class="mt-1">
                            <img id="foto-preview-supervisor" src="{{ asset('path/to/default/avatar.png') }}"
                                 alt="Foto Profil" style="max-width: 100px; max-height: 100px; border-radius: 5px; object-fit: cover;">
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="pt-3 border-top mt-3">
                     {{-- ID Tombol diubah --}}
                    <button type="submit" class="btn btn-primary" id="btnSimpansupervisor">Simpan supervisor Baru</button>
                    {{-- Tombol Delete tidak ada --}}
                    <button type="button" class="btn btn-secondary" id="btnCancelsupervisor" style="display: none;">Batal / Kembali</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Card Tabel supervisor --}}
    <div class="card">
        <div class="card-header"> Daftar supervisor </div>
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
                    <tbody id="isisupervisor">
                         {{-- Include partial tbody supervisor --}}
                        @include('user.tbody-supervisor', ['supervisors' => $supervisors])
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
    // --- PASTIKAN SEMUA NAMA VARIABEL & SELEKTOR SESUAI UNTUK SUPERVISOR ---
    console.log("Document Ready! Script Manajemen SUPERVISOR berjalan."); // Ganti pesan log

    const defaultAvatar = "{{ asset('path/to/default/avatar.png') }}";
    const storageBaseUrl = "{{ asset('storage') }}";
    const formSupervisor = $('#formsupervisor'); // Target ID form supervisor
    const formCardSupervisor = $('#supervisorFormCard'); // Target ID card supervisor
    const formTitleSupervisor = $('#formTitlesupervisor'); // Target ID judul supervisor
    const userIdField = $('#userId'); // ID user tetap sama
    const formModeSupervisorField = $('#formModesupervisor'); // Target ID mode supervisor
    const errorMessagesSupervisor = $('#error-messages-supervisor'); // Target ID error supervisor
    const emailInput = $('#email'); // Input email tetap sama
    const emailErrorDiv = $('#email-error-supervisor'); // Target ID error email supervisor
    const btnSimpanSupervisor = $('#btnSimpansupervisor'); // Target ID tombol simpan supervisor
    const btnCancelSupervisor = $('#btnCancelsupervisor'); // Target ID tombol cancel supervisor
    const passwordRequiredSpan = $('#password-required-supervisor'); // Target ID span password
    const fotoPreviewImg = $('#foto-preview-supervisor'); // Target ID preview foto supervisor
    const inputFotoProfile = $('#foto_profile'); // Input foto tetap sama

    // Selector input (sesuaikan ID form)
    const formInputsSelector = '#formsupervisor input, #formsupervisor select, #formsupervisor textarea';
    const exceptionsSelector = '[type=hidden], #btnCancelsupervisor, #btnSimpansupervisor'; // Sesuaikan tombol

    // --- Fungsi Helper ---

    function clearFieldErrors() {
        emailInput.removeClass('is-invalid');
        emailErrorDiv.hide().text('');
        errorMessagesSupervisor.hide().html(''); // Target div error supervisor
    }

    // Ganti nama fungsi dan logika internal
    function setFormStateSupervisor(mode = 'tambah', userData = null) {
        formSupervisor[0].reset(); // Target form supervisor
        clearFieldErrors();
        formModeSupervisorField.val(mode); // Target mode supervisor
        userIdField.val(userData ? userData.id : '');
        inputFotoProfile.val('');
        fotoPreviewImg.attr('src', defaultAvatar);

        $(formInputsSelector).not(exceptionsSelector).prop('disabled', false);
        // Atur level selalu 'supervisor' dan disabled
        $('#level').val('supervisor').prop('disabled', true); // <-- Value: supervisor

        if (mode === 'tambah') {
            formTitleSupervisor.text('Tambah Supervisor Baru'); // Ganti judul
            btnSimpanSupervisor.text('Simpan Supervisor Baru').show().prop('disabled', false); // Ganti tombol
            btnCancelSupervisor.hide(); // Ganti tombol
            passwordRequiredSpan.show();
        } else if (mode === 'edit') {
            if (!userData) return;
            formTitleSupervisor.text('Edit Supervisor: ' + userData.name); // Ganti judul
            populateFormSupervisor(userData); // Panggil fungsi populate supervisor
            btnSimpanSupervisor.text('Update Supervisor').show().prop('disabled', false); // Ganti tombol
            btnCancelSupervisor.show(); // Ganti tombol
            passwordRequiredSpan.hide();
        }
        // Mode delete tidak ada

        $('html, body').animate({ scrollTop: formCardSupervisor.offset().top - 70 }, 300); // Target card supervisor
    }

    // Ganti nama fungsi
    function populateFormSupervisor(user) {
        $('#name').val(user.name);
        $('#email').val(user.email);
        $('#phone').val(user.phone);
        $('#address').val(user.address);
        // $('#level').val('supervisor'); // Level sudah di-set di setFormStateSupervisor
        $('#status').val(user.status ? '1' : '0');
        $('#password').val('');

        if (user.foto_profile) {
            fotoPreviewImg.attr('src', storageBaseUrl + '/' + user.foto_profile);
        } else {
            fotoPreviewImg.attr('src', defaultAvatar);
        }
    }

    // Ganti nama fungsi
    function displayErrorsSupervisor(errors) {
         clearFieldErrors();
         let generalErrorsHtml = '<ul>';
         let emailErrorMsg = null;
         $.each(errors, function(key, value) {
             let messages = value.join('<br>');
             if (key === 'email') {
                 emailErrorMsg = messages;
                 emailInput.addClass('is-invalid');
                 emailErrorDiv.html(messages).show(); // Target div error supervisor
             } else {
                 generalErrorsHtml += `<li><strong>${key}:</strong> ${messages}</li>`;
             }
         });
         generalErrorsHtml += '</ul>';
         if (generalErrorsHtml !== '<ul></ul>') {
              errorMessagesSupervisor.html(generalErrorsHtml).show(); // Target div error supervisor
         } else if(!emailErrorMsg) {
              errorMessagesSupervisor.html('<ul><li>Terjadi kesalahan validasi.</li></ul>').show();
         }
     }

    // Ganti nama fungsi & route & target tbody
    function refreshSupervisorTable() {
         console.log("Refreshing Supervisor table...");
         // Sesuaikan colspan jika perlu (seharusnya 8)
         $('#isisupervisor').html('<tr><td colspan="8" class="text-center">Memuat data...</td></tr>'); // Target tbody supervisor
         $.ajax({
             url: "{{ route('supervisor.data') }}", // <-- Route data supervisor baru
             type: 'GET',
             success: function(data) {
                 console.log("Supervisor data received for refresh.");
                 $('#isisupervisor').html(data); // Update tbody supervisor
             },
             error: function(xhr) {
                 console.error("Gagal memuat data tabel supervisor:", xhr);
                 $('#isisupervisor').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data supervisor.</td></tr>');
             }
         });
     }

    // --- Event Listeners ---

     // Tombol Edit di Tabel (pastikan class di tbody-supervisor sama, misal .btn-edit-user)
     $(document).on('click', '.btn-edit-user', function() { // Asumsi class tombol edit sama
         console.log("Edit Supervisor clicked");
         let userJson = $(this).data('user');
         let user = (typeof userJson === 'string') ? JSON.parse(userJson) : userJson;
         setFormStateSupervisor('edit', user); // Panggil fungsi state supervisor
     });

     // Tombol Hapus tidak ada

     // Tombol Batal Form Supervisor
     $('#btnCancelSupervisor').click(function() { // Target tombol cancel supervisor
         console.log("Cancel Supervisor clicked");
         setFormStateSupervisor('tambah'); // Panggil fungsi state supervisor
     });

      // Submit Form Supervisor (Tambah/Edit)
      formSupervisor.submit(function(e) { // Target form supervisor
          e.preventDefault();
          btnSimpanSupervisor.prop('disabled', true).text('Menyimpan...'); // Target tombol simpan supervisor
          clearFieldErrors();

          let mode = formModeSupervisorField.val(); // Target mode supervisor
          let userId = userIdField.val();
          let url = '';
          let formData = new FormData(this); // Form ini punya hidden intended_level=supervisor
          let method = 'POST';

          // URL tetap ke user.store dan user.update
          if (mode === 'tambah') {
              url = "{{ route('user.store') }}";
          } else if (mode === 'edit') {
              let urlTemplate = "{{ route('user.update', ['user' => ':id']) }}"; // Sesuaikan user/id
              url = urlTemplate.replace(':id', userId);
              formData.append('_method', 'PUT');
              formData.delete('level'); // Hapus level karena disabled
          } else {
               btnSimpanSupervisor.prop('disabled', false).text('Simpan'); return;
          }

          $.ajax({
              url: url, type: method, data: formData, processData: false, contentType: false,
              success: function (response) {
                  if (response.success) {
                      refreshSupervisorTable(); // Panggil refresh supervisor
                      setFormStateSupervisor('tambah'); // Panggil state supervisor
                      Swal.fire({
                         title: 'Berhasil!',
                         text: response.message, // Pesan dari controller
                         icon: 'success',
                         timer: 1800, // Tampil 1.8 detik
                         showConfirmButton: false // Sembunyikan tombol OK
                     });;
                  } else {
                    Swal.fire({
                         title: 'Gagal!',
                         text: response.message || 'Terjadi kesalahan saat menyimpan data.',
                         icon: 'error'
                      });
                      btnSimpanSupervisor.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Supervisor Baru' : 'Update Supervisor'); // Target tombol supervisor
                  }
              },
              error: function (xhr) {
                  btnSimpanSupervisor.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Supervisor Baru' : 'Update Supervisor'); // Target tombol supervisor
                  if (xhr.status === 422) {
                      displayErrorsSupervisor(xhr.responseJSON.errors); // Panggil display error supervisor
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

       // Listener untuk preview foto
       inputFotoProfile.change(function(){ // Target input foto yg sama
            const file = this.files[0];
            if (file){
                let reader = new FileReader();
                reader.onload = function(event){
                    fotoPreviewImg.attr('src', event.target.result); // Target ID preview supervisor
                }
                reader.readAsDataURL(file);
            } else {
                 fotoPreviewImg.attr('src', defaultAvatar); // Reset ke default
            }
        });

    // --- Inisialisasi ---
    setFormStateSupervisor('tambah'); // Panggil state supervisor

});

</script>
</body>
</html>
@endpush
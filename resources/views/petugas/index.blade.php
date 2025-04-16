@extends('layout.template') {{-- Sesuaikan nama layout --}}

@section('title', 'Manajemen Penugasan Petugas')

{{-- Hapus <!DOCTYPE html>, <html>, <head>, <body> dari sini --}}

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
    <h4 class="my-3">Manajemen Penugasan Petugas</h4>

    {{-- Card Form Inline --}}
    <div class="card mb-4" id="petugasFormCard">
        <div class="card-header">
            <h5 class="card-title mb-0" id="formTitlePetugas">Tambah Penugasan Baru</h5>
        </div>
        <div class="card-body">
            <form id="formPetugas"> {{-- Hapus enctype jika tidak ada file --}}
                @csrf
                <input type="hidden" id="formModePetugas" value="tambah">
                <input type="hidden" id="assignmentId"> {{-- ID record petugas (assignment) --}}

                <div id="error-messages-petugas" class="alert alert-danger" style="display: none;"></div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="user_id" class="form-label">Pilih Petugas (User) <span class="text-danger">*</span></label>
                        {{-- Dropdown untuk Tambah --}}
                        <select name="user_id" id="user_id_add" class="form-select" required>
                            <option value="" selected disabled>-- Pilih User Petugas --</option>
                            @foreach ($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        {{-- Input Disabled untuk Edit/Delete View --}}
                        <input type="text" id="user_id_display" class="form-control" disabled style="display: none;">
                        <div class="form-text" id="user-help-text">Hanya user dengan level 'petugas' yang belum ditugaskan.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cabang_id" class="form-label">Pilih Cabang <span class="text-danger">*</span></label>
                        <select name="cabang_id" id="cabang_id" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Cabang --</option>
                             @foreach ($cabangs as $cabang)
                                <option value="{{ $cabang->id }}">{{ $cabang->nama_perusahaan }} ({{ $cabang->kode_cabang }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="tugas" class="form-label">Deskripsi Tugas</label>
                        <textarea name="tugas" id="tugas" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="pt-3 border-top mt-3">
                    <button type="submit" class="btn btn-primary" id="btnSimpanPetugas">Simpan Penugasan</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDeletePetugas" style="display: none;">Konfirmasi Hapus Tugas Ini</button>
                    <button type="button" class="btn btn-secondary" id="btnCancelPetugas" style="display: none;">Batal / Kembali</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Card Tabel Penugasan --}}
    <div class="card">
        <div class="card-header">
           Daftar Penugasan Petugas
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Petugas</th>
                            <th>Email</th>
                            <th>Cabang Ditugaskan</th>
                            <th>Tugas</th>
                            <th>Tgl Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isiPetugas">
                        {{-- Data awal --}}
                        @include('petugas.tbody', ['assignments' => $assignments])
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
$(document).ready(function () {
    console.log("Document Ready! Script Manajemen Petugas berjalan.");

    // --- Konstanta & Variabel ---
    const formPetugas = $('#formPetugas');
    const formCardPetugas = $('#petugasFormCard');
    const formTitlePetugas = $('#formTitlePetugas');
    const assignmentIdField = $('#assignmentId'); // ID Penugasan (Primary Key tabel petugas)
    const formModePetugasField = $('#formModePetugas');
    const errorMessagesPetugas = $('#error-messages-petugas');
    const btnSimpanPetugas = $('#btnSimpanPetugas');
    const btnCancelPetugas = $('#btnCancelPetugas');
    const btnConfirmDeletePetugas = $('#btnConfirmDeletePetugas');
    const selectUserIdAdd = $('#user_id_add'); // Dropdown untuk Tambah
    const inputUserIdDisplay = $('#user_id_display'); // Input display untuk Edit/Delete
    const selectCabangId = $('#cabang_id');
    const inputTugas = $('#tugas');
    const userHelpText = $('#user-help-text');

    // Selector input
    const formInputsSelectorPetugas = '#formPetugas select, #formPetugas textarea, #formPetugas input[type=text]'; // Lebih spesifik
    const exceptionsSelectorPetugas = '#user_id_display, [type=hidden], #btnCancelPetugas, #btnConfirmDeletePetugas, #btnSimpanPetugas'; // Kecualikan display user

    // --- Fungsi Helper ---

    function setFormStatePetugas(mode = 'tambah', assignmentData = null) {
        formPetugas[0].reset();
        errorMessagesPetugas.hide().html('');
        formModePetugasField.val(mode);
        assignmentIdField.val(assignmentData ? assignmentData.id : '');

        // Reset & Enable all relevant inputs first
        $(formInputsSelectorPetugas).not(exceptionsSelectorPetugas).prop('disabled', false);
        selectUserIdAdd.prop('disabled', false).show(); // Tampilkan & enable dropdown tambah
        inputUserIdDisplay.hide().val(''); // Sembunyikan input display
        userHelpText.show(); // Tampilkan help text untuk tambah

        if (mode === 'tambah') {
            formTitlePetugas.text('Tambah Penugasan Baru');
            btnSimpanPetugas.text('Simpan Penugasan').show().prop('disabled', false);
            btnCancelPetugas.hide();
            btnConfirmDeletePetugas.hide();
        } else if (mode === 'edit') {
            if (!assignmentData || !assignmentData.user) return;
            formTitlePetugas.text('Edit Penugasan: ' + assignmentData.user.name);
            populateFormPetugas(assignmentData);

            // Sembunyikan dropdown tambah, tampilkan input display yg disabled
            selectUserIdAdd.hide().prop('disabled', true);
            inputUserIdDisplay.val(assignmentData.user.name + ' (' + assignmentData.user.email + ')').show();
            userHelpText.hide(); // Sembunyikan help text

            selectCabangId.prop('disabled', false); // Cabang bisa diedit
            inputTugas.prop('disabled', false); // Tugas bisa diedit

            btnSimpanPetugas.text('Update Penugasan').show().prop('disabled', false);
            btnCancelPetugas.show();
            btnConfirmDeletePetugas.hide();
        } else if (mode === 'delete') {
             if (!assignmentData || !assignmentData.user) return;
             formTitlePetugas.text('Hapus Penugasan (Konfirmasi): ' + assignmentData.user.name);
             populateFormPetugas(assignmentData);

             // Disable semua KECUALI tombol & hidden field
             $(formInputsSelectorPetugas).not(exceptionsSelectorPetugas).prop('disabled', true);
              // Tampilkan display user yg disabled
             selectUserIdAdd.hide().prop('disabled', true);
             inputUserIdDisplay.val(assignmentData.user.name + ' (' + assignmentData.user.email + ')').show();
             userHelpText.hide();

             btnSimpanPetugas.hide().prop('disabled', true);
             btnCancelPetugas.show();
             btnConfirmDeletePetugas.show().prop('disabled', false);
        }
         $('html, body').animate({ scrollTop: formCardPetugas.offset().top - 70 }, 300);
    }

    function populateFormPetugas(assignment) {
        // user_id dihandle di setFormStatePetugas
        selectCabangId.val(assignment.cabang_id);
        inputTugas.val(assignment.tugas);
    }

    function displayErrorsPetugas(errors) {
         let errorHtml = '<ul>';
         $.each(errors, function(key, value) {
            // Ganti kunci snake_case ke teks yg lebih ramah
            let fieldName = key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            errorHtml += `<li>${value[0]}</li>`; // Tampilkan hanya pesan error
         });
         errorHtml += '</ul>';
         errorMessagesPetugas.html(errorHtml).show();
     }

    function refreshPetugasTable() {
         console.log("Attempting to refresh Petugas table...");
         $('#isiPetugas').html('<tr><td colspan="7" class="text-center">Memuat data...</td></tr>'); // Colspan 7
         $.ajax({
             url: "{{ route('petugas.data') }}", type: 'GET',
             success: function(data) {
                 console.log("AJAX success for refreshPetugasTable.");
                 $('#isiPetugas').html(data);
             },
             error: function(xhr) {
                 console.error("AJAX error in refreshPetugasTable:", xhr);
                 $('#isiPetugas').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data.</td></tr>');
             }
         });
     }

    // --- Event Listeners ---

    $(document).on('click', '.btn-edit-petugas', function() {
        let assignmentJson = $(this).data('assignment');
        // Log data mentah dari atribut
        console.log("Edit Petugas - Raw data-assignment:", assignmentJson);
        try {
             let assignment = (typeof assignmentJson === 'string') ? JSON.parse(assignmentJson) : assignmentJson;
             // Log data setelah parse dan ID-nya
             console.log("Edit Petugas - Parsed Assignment Object:", assignment);
             console.log("Edit Petugas - Assignment ID:", assignment ? assignment.id : 'N/A');
             console.log("Edit Petugas - User Object:", assignment ? assignment.user : 'N/A');

             if(assignment && assignment.id && assignment.user) { // Pastikan ID dan user ada
                assignmentIdField.val(assignment.id); // Set ID ke hidden field SEKARANG
                setFormStatePetugas('edit', assignment);
            } else {
                console.error("Data assignment tidak lengkap atau tidak valid di tombol edit.", assignmentJson);
                Swal.fire('Error', 'Data penugasan tidak lengkap untuk diedit.', 'error');
            }
        } catch(e) {
            console.error("Error parsing JSON data-assignment:", e, assignmentJson);
            Swal.fire('Error', 'Gagal membaca data penugasan.', 'error');
        }
    });

    $(document).on('click', '.btn-hapus-petugas', function() {
        let assignmentIdFromData = $(this).data('id');
        let petugasName = $(this).data('name');
        let assignmentJson = $(this).data('assignment');
         // Log data mentah
         console.log("Delete Petugas - Raw data-id:", assignmentIdFromData);
         console.log("Delete Petugas - Raw data-assignment:", assignmentJson);
         console.log("Delete Petugas - Raw data-name:", petugasName);

        try {
            let assignment = (typeof assignmentJson === 'string') ? JSON.parse(assignmentJson) : assignmentJson;
            // Log data setelah parse
            console.log("Delete Petugas - Parsed Assignment Object:", assignment);
            console.log("Delete Petugas - Assignment ID from parsed object:", assignment ? assignment.id : 'N/A');

            // Utamakan ID dari objek JSON lengkap
            let finalAssignmentId = assignment ? assignment.id : assignmentIdFromData;
             console.log("Delete Petugas - Final Assignment ID to use:", finalAssignmentId);

            if (finalAssignmentId && assignment && assignment.user) {
                assignmentIdField.val(finalAssignmentId); // Set ID ke hidden field
                setFormStatePetugas('delete', assignment);
            } else {
                console.error("Data assignment tidak lengkap atau tidak valid di tombol hapus.", assignmentJson, assignmentIdFromData);
                Swal.fire('Error', 'Data penugasan tidak lengkap untuk dihapus.', 'error');
            }
        } catch(e) {
             console.error("Error parsing JSON data-assignment for delete:", e, assignmentJson);
             Swal.fire('Error', 'Gagal membaca data penugasan untuk dihapus.', 'error');
        }
    });

    $('#btnCancelPetugas').click(function() { setFormStatePetugas('tambah'); });

    formPetugas.submit(function (e) {
        e.preventDefault();
        btnSimpanPetugas.prop('disabled', true).text('Menyimpan...');
        errorMessagesPetugas.hide().html('');

        let mode = formModePetugasField.val();
        let assignmentId = assignmentIdField.val();
        let url = '';
        let formData = new FormData(this);
        let method = 'POST';

        // Saat edit, user_id diambil dari value input display yg disabled (sudah di-set di setFormState)
        // ATAU lebih aman, ambil dari assignmentData yg tersimpan jika ada,
        // TAPI karena kita tidak simpan state JS, kita tambahkan manual jika mode edit
         if (mode === 'edit') {
             let currentUserId = inputUserIdDisplay.is(':visible') ? $('#editing-user-option').val() : selectUserIdAdd.val();
             if(currentUserId){
                  // Hapus jika sudah ada (dari select yg mungkin terkirim walau disabled)
                 formData.delete('user_id');
                 // Tambahkan user_id yg benar
                 formData.append('user_id', currentUserId);
             }
            console.log('Updating assignment ID:', assignmentId);
            let urlTemplate = "{{ route('petugas.update', ['petuga' => ':id']) }}";
            url = urlTemplate.replace(':id', assignmentId);
            formData.append('_method', 'PUT');
        } else if(mode === 'tambah') {
             console.log('Storing new assignment.');
             // Pastikan user_id dari select add terkirim
             formData.set('user_id', selectUserIdAdd.val());
             url = "{{ route('petugas.store') }}";
        }
         else { return; } // Jangan submit jika mode delete

        // Log FormData sebelum dikirim (untuk debug)
        console.log("Submitting form data for mode:", mode);
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }


        $.ajax({
            url: url, type: method, data: formData, processData: false, contentType: false,
            success: function (response) {
                if (response.success) {
                    refreshPetugasTable();
                    setFormStatePetugas('tambah');
                    Swal.fire('Berhasil!', response.message, 'success');
                } else {
                    Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                    btnSimpanPetugas.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Penugasan' : 'Update Penugasan');
                }
            },
            error: function (xhr) {
                btnSimpanPetugas.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Penugasan' : 'Update Penugasan');
                if (xhr.status === 422) {
                    displayErrorsPetugas(xhr.responseJSON.errors);
                    Swal.fire('Error Validasi', 'Periksa isian form.', 'error');
                } else {
                    console.error("AJAX Error:", xhr);
                    Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Terjadi kesalahan.'), 'error');
                }
            }
        });
    });

    $('#btnConfirmDeletePetugas').click(function() {
         let id = assignmentIdField.val(); // Ambil ID Penugasan dari hidden field
         let petugasName = inputUserIdDisplay.val() || 'Petugas ini'; // Ambil nama dari display input
         console.log("Confirm delete clicked. Assignment ID:", id);
         if (!id) { Swal.fire('Error', 'ID Penugasan tidak ditemukan.', 'error'); return; }

         Swal.fire({
             title: 'Anda Yakin?',
             html: `Anda akan menghapus penugasan untuk: <strong>${petugasName}</strong>?`,
             icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
             cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
         }).then((result) => {
             if (result.isConfirmed) {
                 Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                 let urlTemplate = "{{ route('petugas.destroy', ['petuga' => ':id']) }}"; // Gunakan 'petuga'
                 let url = urlTemplate.replace(':id', id);
                 console.log("Sending DELETE request to:", url);

                 $.ajax({
                     url: url, type: 'POST', data: { _method: 'DELETE', _token: "{{ csrf_token() }}" },
                     success: function (response) {
                         if (response.success) {
                             refreshPetugasTable(); setFormStatePetugas('tambah');
                             Swal.fire('Dihapus!', response.message, 'success');
                         } else { Swal.fire('Gagal!', response.message || 'Error.', 'error'); }
                     },
                     error: function (xhr) {
                         console.error("AJAX Delete Error:", xhr);
                         Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Error.'), 'error');
                     }
                 });
             }
         });
     });

    // --- Inisialisasi ---
    setFormStatePetugas('tambah');

});
</script>
</body>
</html>
@endpush
{{-- Kode JavaScript di bawah ini --}}
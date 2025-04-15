@extends('layout.template') {{-- Sesuaikan nama layout --}}

@section('title', 'Manajemen Penugasan Petugas')

@section('content')
<!DOCTYPE html>
<html>

<head>
    <title>CRUD Petugas - Laravel</title>
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
                        <select name="user_id" id="user_id" class="form-select" required>
                            <option value="" selected disabled>-- Pilih User Petugas --</option>
                            {{-- Opsi user di-load dari controller --}}
                            @foreach ($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                            {{-- Opsi untuk user yang sedang diedit akan ditambahkan oleh JS --}}
                            <option value="" id="editing-user-option" style="display:none;" disabled></option>
                        </select>
                         <div class="form-text">Hanya user dengan level 'petugas' yang belum ditugaskan.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cabang_id" class="form-label">Pilih Cabang <span class="text-danger">*</span></label>
                        <select name="cabang_id" id="cabang_id" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Cabang --</option>
                            {{-- Opsi cabang di-load dari controller --}}
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
    const selectUserId = $('#user_id');
    const selectCabangId = $('#cabang_id');
    const inputTugas = $('#tugas');
    const editingUserOption = $('#editing-user-option'); // Placeholder option

    // Selector input
    const formInputsSelectorPetugas = '#formPetugas select, #formPetugas textarea';
    const exceptionsSelectorPetugas = '[type=hidden], #btnCancelPetugas, #btnConfirmDeletePetugas, #btnSimpanPetugas';

    // --- Fungsi Helper ---

    function setFormStatePetugas(mode = 'tambah', assignmentData = null) {
        formPetugas[0].reset();
        errorMessagesPetugas.hide().html('');
        formModePetugasField.val(mode);
        assignmentIdField.val(assignmentData ? assignmentData.id : '');

        $(formInputsSelectorPetugas).not(exceptionsSelectorPetugas).prop('disabled', false);
        selectUserId.prop('disabled', false); // Pastikan select user aktif di mode tambah/edit
        editingUserOption.hide().val('').text(''); // Sembunyikan opsi user edit

        if (mode === 'tambah') {
            formTitlePetugas.text('Tambah Penugasan Baru');
            btnSimpanPetugas.text('Simpan Penugasan').show().prop('disabled', false);
            btnCancelPetugas.hide();
            btnConfirmDeletePetugas.hide();
            selectUserId.show(); // Tampilkan dropdown user normal
            editingUserOption.hide(); // Pastikan opsi edit user tersembunyi
        } else if (mode === 'edit') {
            if (!assignmentData || !assignmentData.user) return; // Butuh data lengkap
            formTitlePetugas.text('Edit Penugasan: ' + assignmentData.user.name);
            populateFormPetugas(assignmentData);
            // Saat edit, user biasanya tidak diubah, jadi disable dropdown user
            // Tapi kita tambahkan opsi user yg sedang diedit agar nilainya terkirim
            editingUserOption
                .val(assignmentData.user_id)
                .text(assignmentData.user.name + ' ('+ assignmentData.user.email +')')
                .prop('selected', true) // Pilih opsi ini
                .show();
            selectUserId.val(assignmentData.user_id).prop('disabled', true); // Pilih dan disable select utama
            // Jika ingin user bisa diubah saat edit, jangan disable selectUserId & hapus logic editingUserOption
            selectCabangId.prop('disabled', false); // Cabang bisa diubah
            inputTugas.prop('disabled', false); // Tugas bisa diubah

            btnSimpanPetugas.text('Update Penugasan').show().prop('disabled', false);
            btnCancelPetugas.show();
            btnConfirmDeletePetugas.hide();
        } else if (mode === 'delete') {
             if (!assignmentData || !assignmentData.user) return;
             formTitlePetugas.text('Hapus Penugasan (Konfirmasi): ' + assignmentData.user.name);
             populateFormPetugas(assignmentData);
             // Disable semua form element
             $(formInputsSelectorPetugas).not(exceptionsSelectorPetugas).prop('disabled', true);
              // Disable select user secara eksplisit juga
             selectUserId.prop('disabled', true);
             editingUserOption // Tampilkan user yg akan dihapus
                 .val(assignmentData.user_id)
                 .text(assignmentData.user.name + ' ('+ assignmentData.user.email +')')
                 .prop('selected', true)
                 .show();

             btnSimpanPetugas.hide().prop('disabled', true);
             btnCancelPetugas.show();
             btnConfirmDeletePetugas.show().prop('disabled', false);
        }
         $('html, body').animate({ scrollTop: formCardPetugas.offset().top - 70 }, 300);
    }

    function populateFormPetugas(assignment) {
        // user_id sudah dihandle di setFormStatePetugas (mode edit/delete)
        selectCabangId.val(assignment.cabang_id);
        inputTugas.val(assignment.tugas);
    }

    function displayErrorsPetugas(errors) {
         let errorHtml = '<ul>';
         $.each(errors, function(key, value) { errorHtml += '<li>' + value[0] + '</li>'; });
         errorHtml += '</ul>';
         errorMessagesPetugas.html(errorHtml).show();
     }

    function refreshPetugasTable() {
         $('#isiPetugas').html('<tr><td colspan="7" class="text-center">Memuat data...</td></tr>'); // Sesuaikan colspan
         $.ajax({
             url: "{{ route('petugas.data') }}", type: 'GET',
             success: function(data) { $('#isiPetugas').html(data); },
             error: function(xhr) {
                 console.error("Gagal memuat data tabel petugas:", xhr);
                 $('#isiPetugas').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data.</td></tr>');
             }
         });
     }

    // --- Event Listeners ---

    $(document).on('click', '.btn-edit-petugas', function() {
        let assignmentJson = $(this).data('assignment');
        let assignment = (typeof assignmentJson === 'string') ? JSON.parse(assignmentJson) : assignmentJson;
        setFormStatePetugas('edit', assignment);
    });

    $(document).on('click', '.btn-hapus-petugas', function() {
        let assignmentId = $(this).data('id');
        let petugasName = $(this).data('name'); // Ambil nama dari data-*
        // Ambil data lengkap dari tombol edit jika perlu untuk tampilan form delete
        let assignmentJson = $(this).siblings('.btn-edit-petugas').data('assignment');
         let assignment = (typeof assignmentJson === 'string') ? JSON.parse(assignmentJson) : assignmentJson;

         assignmentIdField.val(assignmentId); // Set ID untuk tombol konfirmasi
         setFormStatePetugas('delete', assignment); // Masuk mode delete view
    });

    $('#btnCancelPetugas').click(function() { setFormStatePetugas('tambah'); });

    formPetugas.submit(function (e) {
        e.preventDefault();
        btnSimpanPetugas.prop('disabled', true).text('Menyimpan...');
        errorMessagesPetugas.hide().html('');

        let mode = formModePetugasField.val();
        let assignmentId = assignmentIdField.val(); // ID Penugasan untuk update
        let url = '';
        let formData = new FormData(this); // Ambil data form
        let method = 'POST';

         // Jika user_id disabled (mode edit), tambahkan manual ke FormData
         if (selectUserId.is(':disabled') && mode === 'edit') {
             formData.append('user_id', selectUserId.val());
         }

        if (mode === 'tambah') {
            url = "{{ route('petugas.store') }}";
        } else if (mode === 'edit') {
            // Gunakan {petuga} sesuai route dan variabel controller
            let urlTemplate = "{{ route('petugas.update', ['petuga' => ':id']) }}";
            url = urlTemplate.replace(':id', assignmentId);
            formData.append('_method', 'PUT');
        } else { return; } // Jangan submit jika mode delete

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
                    console.error(xhr);
                    Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Terjadi kesalahan.'), 'error');
                }
            }
        });
    });

    $('#btnConfirmDeletePetugas').click(function() {
         let id = assignmentIdField.val(); // ID Penugasan
         let petugasName = $('#user_id option:selected').text() || $('#editing-user-option').text(); // Ambil nama dari opsi terpilih/disabled
         if (!id) { Swal.fire('Error', 'ID Penugasan tidak ditemukan.', 'error'); return; }

         Swal.fire({
             title: 'Anda Yakin?',
             html: `Anda akan menghapus penugasan untuk: <strong>${petugasName}</strong>?`,
             icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
             cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
         }).then((result) => {
             if (result.isConfirmed) {
                 Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                 // Gunakan {petuga} sesuai route
                 let urlTemplate = "{{ route('petugas.destroy', ['petuga' => ':id']) }}";
                 let url = urlTemplate.replace(':id', id);

                 $.ajax({
                     url: url, type: 'POST', data: { _method: 'DELETE', _token: "{{ csrf_token() }}" },
                     success: function (response) {
                         if (response.success) {
                             refreshPetugasTable(); setFormStatePetugas('tambah');
                             Swal.fire('Dihapus!', response.message, 'success');
                         } else { Swal.fire('Gagal!', response.message || 'Error.', 'error'); }
                     },
                     error: function (xhr) {
                         console.error(xhr); Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Error.'), 'error');
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
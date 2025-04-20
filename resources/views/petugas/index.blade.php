@extends('layout.template') {{-- Sesuaikan nama layout --}}

@section('title', 'Manajemen Penugasan Petugas')

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
            {{-- Berikan id unik ke form filter jika diperlukan JS terpisah --}}
            {{-- <form id="formPetugas" data-user-level="{{ $userLevel }}" data-user-cabang-id="{{ $userCabangId }}"> --}}
            <form id="formPetugas"> {{-- ID form utama --}}
                @csrf
                <input type="hidden" id="formModePetugas" value="tambah">
                <input type="hidden" id="assignmentId"> {{-- ID record petugas (assignment) --}}
                {{-- Simpan info user untuk JS jika perlu --}}
                <input type="hidden" id="currentUserLevel" value="{{ $userLevel }}">
                <input type="hidden" id="currentUserCabangId" value="{{ $userCabangId }}">

                <div id="error-messages-petugas" class="alert alert-danger" style="display: none;"></div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="user_id_add" class="form-label">Pilih Petugas (User) <span class="text-danger">*</span></label>
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

                    <div class="col-md-6 mb-3" id="cabangFieldContainer"> {{-- Beri ID pada container --}}
                        <label for="cabang_id_select" class="form-label"> {{-- Ganti 'for' ke ID select --}}
                            @if($userLevel === 'admin')
                                Pilih Cabang <span class="text-danger">*</span>
                            @else
                                Cabang Ditugaskan
                            @endif
                        </label>
                    
                        {{-- 1. Select (Untuk Admin) --}}
                        <select name="{{ $userLevel === 'admin' ? 'cabang_id' : '' }}" {{-- Atribut name hanya jika admin --}}
                                id="cabang_id_select" {{-- ID Unik --}}
                                class="form-select"
                                required {{-- Mungkin hapus required di sini, atur via JS jika perlu --}}
                                style="{{ $userLevel !== 'admin' ? 'display: none;' : '' }}" {{-- Sembunyikan jika bukan admin --}}
                                >
                            <option value="" selected disabled>-- Pilih Cabang --</option>
                            @foreach ($cabangs as $cabang) {{-- $cabangs tetap berisi semua cabang untuk admin --}}
                                <option value="{{ $cabang->id }}">{{ $cabang->nama_perusahaan }} ({{ $cabang->kode_cabang }})</option>
                            @endforeach
                        </select>
                    
                        {{-- 2. Text Display (Untuk Non-Admin View) --}}
                        <input type="text"
                               id="cabang_id_display_text" {{-- ID Unik --}}
                               class="form-control"
                               value="{{ $userCabangDetail ? $userCabangDetail->nama_perusahaan.' ('.$userCabangDetail->kode_cabang.')' : 'Cabang tidak ditemukan' }}"
                               style="{{ $userLevel === 'admin' ? 'display: none;' : '' }}" {{-- Sembunyikan jika admin --}}
                               disabled> {{-- Selalu disabled --}}
                    
                        {{-- 3. Hidden Input (Untuk Non-Admin Submit) --}}
                        <input type="hidden"
                               name="{{ $userLevel !== 'admin' ? 'cabang_id' : '' }}" {{-- Atribut name hanya jika non-admin --}}
                               id="cabang_id_hidden" {{-- ID Unik --}}
                               value="{{ $userCabangId }}"> {{-- Nilai dari controller --}}
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

    {{-- Filter Cabang (Hanya Admin) --}}
    @if($userLevel === 'admin')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('petugas.index') }}" id="filterForm"> {{-- Form untuk GET request filter --}}
                <div class="row g-2">
                    <div class="col-md-4">
                        <label for="filter_cabang_id" class="form-label">Filter Berdasarkan Cabang:</label>
                        <select name="cabang_id" id="filter_cabang_id" class="form-select">
                            <option value="">-- Tampilkan Semua Cabang --</option>
                            @foreach ($cabangs as $cabang) {{-- $cabangs berisi semua cabang aktif untuk admin --}}
                                <option value="{{ $cabang->id }}" {{ $selectedCabangId == $cabang->id ? 'selected' : '' }}>
                                    {{ $cabang->nama_perusahaan }} ({{ $cabang->kode_cabang }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-info">Filter</button>
                        {{-- Tombol reset filter --}}
                         @if($selectedCabangId)
                         <a href="{{ route('petugas.index') }}" class="btn btn-secondary ms-2">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif


    {{-- Card Tabel Penugasan --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
           <span> Daftar Penugasan Petugas
            @if($userLevel !== 'admin' && $userCabangDetail)
                - Cabang: <strong>{{ $userCabangDetail->nama_perusahaan }}</strong>
            @elseif($userLevel === 'admin' && $selectedCabangId && ($filteredCabang = $cabangs->firstWhere('id', $selectedCabangId)))
                - Filtered: <strong>{{ $filteredCabang->nama_perusahaan }}</strong>
            @elseif($userLevel === 'admin' && !$selectedCabangId)
                 - (Semua Cabang)
            @endif
           </span>
            {{-- Tombol Print bisa ditambahkan di sini jika perlu --}}
            {{-- <button id="btnPrintReport" class="btn btn-sm btn-outline-secondary">Cetak Laporan</button> --}}
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

{{-- Hapus <!DOCTYPE html>, <html>, <head>, <body> dari sini (sudah benar) --}}
@endsection

@push('js')
{{-- Hapus <head>, <body>, <html> dari script --}}
{{-- Pindahkan link CSS dan JS ke layout utama jika belum --}}
{{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
{{-- <style> ... </style> --}}

<script>
$(document).ready(function () {
    console.log("Document Ready! Script Manajemen Petugas berjalan.");

    // --- Konstanta & Variabel ---
    const formPetugas = $('#formPetugas');
    const formCardPetugas = $('#petugasFormCard');
    const formTitlePetugas = $('#formTitlePetugas');
    const assignmentIdField = $('#assignmentId');
    const formModePetugasField = $('#formModePetugas');
    const errorMessagesPetugas = $('#error-messages-petugas');
    const btnSimpanPetugas = $('#btnSimpanPetugas');
    const btnCancelPetugas = $('#btnCancelPetugas');
    const btnConfirmDeletePetugas = $('#btnConfirmDeletePetugas');
    const selectUserIdAdd = $('#user_id_add');
    const inputUserIdDisplay = $('#user_id_display');
    const selectCabangId = $('#cabang_id'); // Bisa jadi select (admin) atau hidden input (lainnya)
    const inputTugas = $('#tugas');
    const userHelpText = $('#user-help-text');
    const filterCabangSelect = $('#filter_cabang_id'); // Filter dropdown

    // Ambil info user dari hidden input
    const currentUserLevel = $('#currentUserLevel').val();
    const currentUserCabangId = $('#currentUserCabangId').val();

    // Selector input (perlu disesuaikan berdasarkan role)
    // Admin bisa edit user, cabang, tugas. Non-admin hanya tugas.
    const adminEditableInputs = '#formPetugas select[name="user_id"], #formPetugas select[name="cabang_id"], #formPetugas textarea[name="tugas"]';
    const nonAdminEditableInputs = '#formPetugas textarea[name="tugas"]'; // Hanya tugas
    const alwaysDisabledInDelete = '#user_id_display'; // Input display user selalu disabled

    // --- Fungsi Helper ---

    function setFormStatePetugas(mode = 'tambah', assignmentData = null) {
    formPetugas[0].reset();
    errorMessagesPetugas.hide().html('');
    formModePetugasField.val(mode);
    assignmentIdField.val(assignmentData ? assignmentData.id : '');

    // Cache selectors
    const cabangSelect = $('#cabang_id_select');
    const cabangDisplayText = $('#cabang_id_display_text');
    const cabangHidden = $('#cabang_id_hidden');
    const cabangLabel = $('#cabangFieldContainer label');

    // --- Reset State Awal (sebelum mode spesifik) ---
    $('#formPetugas input:not([type=hidden]), #formPetugas select, #formPetugas textarea')
       .not('#cabang_id_display_text') // Jangan disable text display cabang di awal
       .prop('disabled', true); // Disable semua kecuali hidden dan text display

    selectUserIdAdd.show().prop('disabled', true);
    inputUserIdDisplay.hide().val('');
    userHelpText.show();

    // Atur state elemen cabang berdasarkan ROLE
    if (currentUserLevel === 'admin') {
        cabangLabel.html('Pilih Cabang <span class="text-danger">*</span>');
        cabangSelect.show().prop('disabled', true).attr('name', 'cabang_id'); // Show select, disable, set name
        cabangDisplayText.hide();
        cabangHidden.removeAttr('name'); // Hapus name dari hidden
    } else {
        cabangLabel.text('Cabang Ditugaskan');
        cabangSelect.hide().removeAttr('name'); // Hide select, hapus name
        cabangDisplayText.show().prop('disabled', true); // Show text (selalu disabled)
        // Pastikan hidden input punya value dan name
        cabangHidden.val(currentUserCabangId).attr('name', 'cabang_id'); // Set name ke hidden
    }

    // --- Mode Specific Logic ---
    if (mode === 'tambah') {
        formTitlePetugas.text('Tambah Penugasan Baru');
        selectUserIdAdd.prop('disabled', false);
        inputUserIdDisplay.hide();
        userHelpText.show();
        inputTugas.prop('disabled', false);

        if (currentUserLevel === 'admin') {
            cabangSelect.prop('disabled', false); // Enable SELECT
            // Reset pilihan select jika perlu
            cabangSelect.val('');
        } else {
            // Non-admin: Text display disabled, hidden input active (sudah diatur)
             // Setel ulang nilai text display ke nilai default user saat kembali ke 'tambah'
            let defaultCabangText = "{{ $userCabangDetail ? $userCabangDetail->nama_perusahaan.' ('.$userCabangDetail->kode_cabang.')' : 'Cabang tidak ditemukan' }}";
            cabangDisplayText.val(defaultCabangText);
             cabangHidden.val(currentUserCabangId); // Pastikan ID hidden benar
        }

        btnSimpanPetugas.text('Simpan Penugasan').show().prop('disabled', false);
        btnCancelPetugas.hide();
        btnConfirmDeletePetugas.hide();

    } else if (mode === 'edit') {
        if (!assignmentData || !assignmentData.user || !assignmentData.cabang) { // Tambah cek cabang
            console.error("Data assignment tidak lengkap untuk edit:", assignmentData);
            Swal.fire('Error', 'Data penugasan tidak lengkap.', 'error');
            setFormStatePetugas('tambah');
            return;
        }
        formTitlePetugas.text('Edit Penugasan: ' + assignmentData.user.name);
        populateFormPetugas(assignmentData); // Isi form

        selectUserIdAdd.hide().prop('disabled', true);
        inputUserIdDisplay.val(assignmentData.user.name + ' (' + assignmentData.user.email + ')').show().prop('disabled', true);
        userHelpText.hide();
        inputTugas.prop('disabled', false);

        if (currentUserLevel === 'admin') {
            cabangSelect.prop('disabled', false); // Enable SELECT
        } else {
            // Non-admin: Text display disabled, hidden input active
        }

        btnSimpanPetugas.text('Update Penugasan').show().prop('disabled', false);
        btnCancelPetugas.show();
        btnConfirmDeletePetugas.hide();

    } else if (mode === 'delete') {
         if (!assignmentData || !assignmentData.user || !assignmentData.cabang) {
             console.error("Data assignment tidak lengkap untuk delete:", assignmentData);
             Swal.fire('Error', 'Data penugasan tidak lengkap.', 'error');
             setFormStatePetugas('tambah');
             return;
         }
        formTitlePetugas.text('Hapus Penugasan: ' + assignmentData.user.name);
        populateFormPetugas(assignmentData);

        // Disable semua input & select KECUALI tombol aksi & hidden
         $('#formPetugas input:not([type=hidden], [type=button]), #formPetugas select, #formPetugas textarea')
             .not('#btnCancelPetugas, #btnConfirmDeletePetugas') // Kecualikan tombol
             .prop('disabled', true);

        // Pastikan elemen cabang yang benar terlihat & disabled
         if (currentUserLevel === 'admin') {
             cabangSelect.show().prop('disabled', true); // Select terlihat, disabled
             cabangDisplayText.hide();
         } else {
             cabangSelect.hide();
             cabangDisplayText.show().prop('disabled', true); // Text terlihat, disabled
         }

        selectUserIdAdd.hide().prop('disabled', true);
        inputUserIdDisplay.show().prop('disabled', true);
        userHelpText.hide();

        btnSimpanPetugas.hide();
        btnCancelPetugas.show();
        btnConfirmDeletePetugas.show().prop('disabled', false);
    }
    // Scroll ke form
    $('html, body').animate({ scrollTop: formCardPetugas.offset().top - 70 }, 300);
}

// Sesuaikan populateFormPetugas dengan ID baru
function populateFormPetugas(assignment) {
    console.log("Populating form with assignment:", assignment); // Debug log
    inputTugas.val(assignment.tugas || ''); // Use || '' as fallback

    if (currentUserLevel === 'admin') {
        $('#cabang_id_select').val(assignment.cabang_id);
    } else {
        // --- FIX HERE ---
        // Construct the display text directly from the assignment object properties
        let cabangDisplayTextVal = 'Cabang Tidak Ditemukan'; // Default text
        if (assignment.cabang) {
            // Make sure both properties exist before concatenating
            let namaPerusahaan = assignment.cabang.nama_perusahaan || 'Nama?';
            let kodeCabang = assignment.cabang.kode_cabang || 'Kode?'; // Assuming 'kode_cabang' is the correct field name based on your view logic
            cabangDisplayTextVal = `${namaPerusahaan} (${kodeCabang})`;
        }
         else {
             console.warn("Assignment object is missing 'cabang' details in populateFormPetugas:", assignment);
         }

        $('#cabang_id_display_text').val(cabangDisplayTextVal);
        $('#cabang_id_hidden').val(assignment.cabang_id); // Set hidden juga saat populate
    }

     // Also ensure user display is populated correctly if needed here, although setFormState does it later
     if (assignment.user) {
         $('#user_id_display').val(`${assignment.user.name || 'Nama?'} (${assignment.user.email || 'Email?'})`);
     } else {
         console.warn("Assignment object is missing 'user' details in populateFormPetugas:", assignment);
         $('#user_id_display').val('User Tidak Ditemukan'); // Set fallback text
     }
}


    function displayErrorsPetugas(errors) {
         let errorHtml = '<ul>';
         $.each(errors, function(key, value) {
             // Ganti kunci snake_case ke teks yg lebih ramah
             let fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
             errorHtml += `<li>${value[0]}</li>`; // Tampilkan hanya pesan error pertama per field
         });
         errorHtml += '</ul>';
         errorMessagesPetugas.html(errorHtml).show();
     }

    // Fungsi refresh tabel dengan filter opsional
    function refreshPetugasTable(cabangIdFilter = null) {
        console.log("Attempting to refresh Petugas table... Filter:", cabangIdFilter);
        $('#isiPetugas').html('<tr><td colspan="7" class="text-center">Memuat data...</td></tr>');
        let url = "{{ route('petugas.data') }}";
        let data = {};
        if (cabangIdFilter) {
            data.cabang_id = cabangIdFilter; // Tambahkan parameter filter jika ada
        }

        $.ajax({
            url: url,
            type: 'GET',
            data: data, // Kirim data filter
            success: function(data) {
                console.log("AJAX success for refreshPetugasTable.");
                $('#isiPetugas').html(data);
            },
            error: function(xhr) {
                console.error("AJAX error in refreshPetugasTable:", xhr);
                 let errorMsg = 'Gagal memuat data.';
                 if(xhr.status >= 500) errorMsg += ' Kesalahan Server.';
                 else if(xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                $('#isiPetugas').html(`<tr><td colspan="7" class="text-center text-danger">${errorMsg}</td></tr>`);
            }
        });
    }

    // --- Event Listeners ---

    // Edit Button
    $(document).on('click', '.btn-edit-petugas', function() {
    const button = $(this);
    console.log("Edit Petugas - Button Clicked");
    let rawDataAttr = button.attr('data-assignment'); // Get raw attribute value
    let decodedJsonString; // Declare here to be accessible in catch

    try {
        // 1. Ambil nilai atribut mentah
        if (!rawDataAttr) {
            console.error("Atribut data-assignment kosong atau tidak ditemukan!");
            Swal.fire('Error', 'Data penugasan tidak ditemukan pada tombol.', 'error');
            return;
        }
        console.log("Edit Petugas - Raw data-assignment attr:", rawDataAttr);

        // 2. Decode HTML entities menggunakan DOMParser
        try {
            const parser = new DOMParser();
            const dom = parser.parseFromString('<!doctype html><body>' + rawDataAttr, 'text/html');
            decodedJsonString = dom.body.textContent;
        } catch (decodeError) {
            console.error("DOMParser decoding failed:", decodeError);
            console.error("Raw attribute during decode error:", rawDataAttr);
            Swal.fire('Error', 'Gagal mendekode data internal (DOMParser).', 'error');
            return;
        }

        console.log("Edit Petugas - Decoded JSON string (DOMParser):", decodedJsonString);

        if (!decodedJsonString) {
            console.error("String JSON kosong setelah decode (DOMParser)!");
            Swal.fire('Error', 'Gagal mendekode data penugasan (hasil kosong).', 'error');
            return;
        }

        // 3. Parse string JSON yang sudah bersih
        let assignment = JSON.parse(decodedJsonString);

        // 4. Lakukan validasi objek hasil parse
        // Added checks for user.name and cabang.nama_perusahaan for robustness
        if(assignment && assignment.id && assignment.user && assignment.user.name && assignment.cabang && assignment.cabang.nama_perusahaan) {
            console.log("Edit Petugas - Parsed Assignment Object:", assignment);
            setFormStatePetugas('edit', assignment);
        } else {
            console.error("Data assignment tidak lengkap/valid setelah parsing.", assignment);
            Swal.fire('Error', 'Struktur data penugasan tidak lengkap (Missing ID, User/Name, or Cabang/Name).', 'error');
        }

    } catch(e) { // Catch error from JSON.parse or validation checks
        console.error("Error processing data-assignment for Edit:", e);
        console.error("Raw attribute value during error:", rawDataAttr || 'Atribut tidak terbaca');
        // Log decoded string if available
        if (typeof decodedJsonString !== 'undefined') {
             console.error("Decoded string value during error:", decodedJsonString);
        }
        Swal.fire('Error', 'Gagal memproses data penugasan (JSON Parse/Validation). Periksa console log.', 'error');
    }
});


    // Hapus Button
    $(document).on('click', '.btn-hapus-petugas', function() {
    const button = $(this);
    let assignmentIdFromData = button.data('id');
    console.log("Delete Petugas - Button Clicked");
    let rawDataAttr = button.attr('data-assignment'); // Get raw attribute value
    let decodedJsonString; // Declare here

    try {
        // 1. Ambil nilai atribut mentah
        if (!rawDataAttr) {
             console.error("Atribut data-assignment kosong atau tidak ditemukan!");
             Swal.fire('Error', 'Data penugasan tidak ditemukan pada tombol.', 'error');
             return;
         }
        console.log("Delete Petugas - Raw data-assignment attr:", rawDataAttr);

        // 2. Decode HTML entities menggunakan DOMParser
        try {
             const parser = new DOMParser();
             const dom = parser.parseFromString('<!doctype html><body>' + rawDataAttr, 'text/html');
             decodedJsonString = dom.body.textContent;
         } catch (decodeError) {
             console.error("DOMParser decoding failed:", decodeError);
             console.error("Raw attribute during decode error:", rawDataAttr);
             Swal.fire('Error', 'Gagal mendekode data internal (DOMParser).', 'error');
             return;
         }

        console.log("Delete Petugas - Decoded JSON string (DOMParser):", decodedJsonString);

        if (!decodedJsonString) {
            console.error("String JSON kosong setelah decode (DOMParser)!");
            Swal.fire('Error', 'Gagal mendekode data penugasan (hasil kosong).', 'error');
            return;
        }

        // 3. Parse string JSON yang sudah bersih
        let assignment = JSON.parse(decodedJsonString);

        // 4. Lakukan validasi objek hasil parse & tentukan ID final
        let finalAssignmentId = assignment ? assignment.id : assignmentIdFromData; // Fallback ke data-id jika parse gagal total tapi ID ada
        console.log("Delete Petugas - Final Assignment ID to use:", finalAssignmentId);

        // Added checks for user.name and cabang.nama_perusahaan
        if (finalAssignmentId && assignment && assignment.user && assignment.user.name && assignment.cabang && assignment.cabang.nama_perusahaan) {
            console.log("Delete Petugas - Parsed Assignment Object:", assignment);
            // Pastikan ID di form di-set DENGAN BENAR sebelum memanggil setFormState
            assignmentIdField.val(finalAssignmentId); // Explicitly set the ID field
            setFormStatePetugas('delete', assignment); // Panggil setFormState
        } else {
            console.error("Data assignment tidak lengkap/valid setelah parsing.", assignment);
            // Jika parsing gagal tapi ID ada dari data-id, coba set state delete minimalis
            if (finalAssignmentId) {
                 console.warn("Trying minimal delete state setup due to incomplete data.");
                 assignmentIdField.val(finalAssignmentId);
                 setFormStatePetugas('delete', { id: finalAssignmentId, user: { name: 'Data Tidak Lengkap' }, cabang: { nama_perusahaan: 'N/A'} }); // Provide dummy data
                 $('#user_id_display').val('Data User Tidak Lengkap'); // Override user display
                 Swal.fire('Peringatan', 'Data penugasan tidak lengkap, namun ID ditemukan. Konfirmasi penghapusan?', 'warning');
            } else {
                 Swal.fire('Error', 'Struktur data penugasan tidak lengkap atau ID tidak ditemukan.', 'error');
            }
        }

    } catch(e) { // Catch error from JSON.parse or validation checks
        console.error("Error processing data-assignment for Delete:", e);
        console.error("Raw attribute value during error:", rawDataAttr || 'Atribut tidak terbaca');
        if (typeof decodedJsonString !== 'undefined') {
             console.error("Decoded string value during error:", decodedJsonString);
        }
        Swal.fire('Error', 'Gagal memproses data penugasan (JSON Parse/Validation). Periksa console log.', 'error');
    }
});
    // Cancel Button
    $('#btnCancelPetugas').click(function() { setFormStatePetugas('tambah'); });

    // Form Submit (Tambah / Update)
    formPetugas.submit(function (e) {
        e.preventDefault();
        btnSimpanPetugas.prop('disabled', true).text('Menyimpan...');
        errorMessagesPetugas.hide().html('');

        let mode = formModePetugasField.val();
        let assignmentId = assignmentIdField.val();
        let url = '';
        let formData = new FormData(this);
        let method = 'POST';

        // Hapus user_id dari FormData jika mode edit (karena input display yg dikirim)
        // Controller akan pakai user_id dari objek Petugas yg di-load
        // Cabang_id akan terkirim dari select (admin) atau hidden input (lainnya)
        if (mode === 'edit') {
            formData.delete('user_id'); // Jangan kirim user_id dari form saat update
            console.log('Updating assignment ID:', assignmentId);
            let urlTemplate = "{{ route('petugas.update', ['petuga' => ':id']) }}";
            url = urlTemplate.replace(':id', assignmentId);
            formData.append('_method', 'PUT'); // Method Spoofing
        } else if(mode === 'tambah') {
             console.log('Storing new assignment.');
             // user_id dan cabang_id sudah benar di FormData (dari select/hidden)
             url = "{{ route('petugas.store') }}";
        }
        else {
            btnSimpanPetugas.prop('disabled', false).text('Aksi Tidak Valid');
            return; // Jangan submit jika mode delete
        }

        // Log FormData sebelum dikirim
        console.log("Submitting form data for mode:", mode, "to URL:", url);
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        $.ajax({
            url: url, type: method, data: formData, processData: false, contentType: false,
            success: function (response) {
                if (response.success) {
                    // Refresh tabel dengan filter yang sedang aktif (jika admin)
                    let currentFilter = (currentUserLevel === 'admin') ? filterCabangSelect.val() : null;
                    refreshPetugasTable(currentFilter);
                    setFormStatePetugas('tambah'); // Reset form ke mode tambah
                    Swal.fire('Berhasil!', response.message, 'success');
                } else {
                    // Tampilkan pesan error dari server jika ada
                    Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                    // Kembalikan tombol simpan ke state semula
                    btnSimpanPetugas.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Penugasan' : 'Update Penugasan');
                }
            },
            error: function (xhr) {
                btnSimpanPetugas.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Penugasan' : 'Update Penugasan');
                if (xhr.status === 422) { // Error validasi
                    displayErrorsPetugas(xhr.responseJSON.errors);
                    Swal.fire('Error Validasi', 'Periksa kembali isian form Anda.', 'error');
                } else if (xhr.status === 403) { // Error otorisasi
                     Swal.fire('Akses Ditolak!', xhr.responseJSON.message || 'Anda tidak memiliki izin untuk melakukan aksi ini.', 'error');
                } else {
                    console.error("AJAX Error:", xhr);
                    Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Terjadi kesalahan pada server.'), 'error');
                }
            }
        });
    });

    // Confirm Delete Button in Form
    $('#btnConfirmDeletePetugas').click(function() {
    let id = assignmentIdField.val(); // Ambil ID dari hidden field di form (sudah di-set oleh .btn-hapus-petugas handler)
    // Ambil nama dari display input, TAPI berikan fallback jika kosong
    let petugasName = inputUserIdDisplay.val() || 'Petugas Ini (Nama Tidak Ditemukan)';
    console.log("Confirm delete clicked. Assignment ID:", id, "Petugas Name from input:", inputUserIdDisplay.val());

    if (!id) {
        Swal.fire('Error', 'ID Penugasan tidak ditemukan untuk dihapus.', 'error');
        return;
    }

    // Swal confirmation uses the retrieved name
    Swal.fire({
        title: 'Anda Yakin?',
        html: `Anda akan menghapus penugasan untuk:<br><strong>${petugasName}</strong>?`, // Use the potentially fallback name
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Menghapus...', html: 'Mohon tunggu sebentar...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            let urlTemplate = "{{ route('petugas.destroy', ['petuga' => ':id']) }}";
            let url = urlTemplate.replace(':id', id);
            console.log("Sending DELETE request to:", url);

            $.ajax({
                url: url, type: 'POST',
                data: { _method: 'DELETE', _token: "{{ csrf_token() }}" },
                success: function (response) {
                    if (response.success) {
                        let currentFilter = (currentUserLevel === 'admin') ? filterCabangSelect.val() : null;
                        refreshPetugasTable(currentFilter);
                        setFormStatePetugas('tambah');
                        Swal.fire('Dihapus!', response.message, 'success');
                    } else { Swal.fire('Gagal!', response.message || 'Gagal menghapus.', 'error'); }
                },
                error: function (xhr) {
                    console.error("AJAX Delete Error:", xhr);
                     if (xhr.status === 403) {
                         Swal.fire('Akses Ditolak!', xhr.responseJSON.message || 'Anda tidak memiliki izin untuk menghapus data ini.', 'error');
                     } else {
                         Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Gagal menghapus data.'), 'error');
                     }
                }
            });
        }
    });
});

     // --- Filter Handling (Admin Only) ---
    // Jika menggunakan AJAX untuk filter (opsional, alternatif dari form GET biasa)
    /*
    if (currentUserLevel === 'admin') {
        filterCabangSelect.change(function() {
            let selectedCabangId = $(this).val();
            console.log("Filter changed:", selectedCabangId);
            refreshPetugasTable(selectedCabangId); // Panggil refresh dengan ID terpilih
        });
    }
    */
    // Kode di atas mengasumsikan filter via AJAX. Jika pakai form GET (seperti di blade), JS ini tidak perlu.

    // --- Inisialisasi ---
    setFormStatePetugas('tambah'); // Set form ke mode tambah saat halaman dimuat

});
</script>
{{-- </html> --}}
</body>
</html>
@endpush
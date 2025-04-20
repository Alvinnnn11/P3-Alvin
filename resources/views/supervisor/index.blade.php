@extends('layout.template') {{-- Sesuaikan nama layout --}}

@section('title', 'Manajemen Penugasan Supervisor')

{{-- Hapus <!DOCTYPE html>, <html>, <head>, <body> dari sini --}}

@section('content')
{{-- Hapus duplikasi <!DOCTYPE html>, <html>, <head>, <body> --}}
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
            <h4 class="my-3">Manajemen Penugasan Supervisor</h4>
        
            {{-- Card Form Inline --}}
            <div class="card mb-4" id="supervisorFormCard">
                <div class="card-header">
                    <h5 class="card-title mb-0" id="formTitlesupervisor">Tambah Penugasan Baru</h5>
                </div>
                <div class="card-body">
                    <form id="formsupervisor"> {{-- ID form utama --}}
                        @csrf
                        <input type="hidden" id="formModesupervisor" value="tambah">
                        <input type="hidden" id="assignmentId"> {{-- ID record supervisor (assignment) --}}
                        {{-- Simpan info user untuk JS --}}
                        <input type="hidden" id="currentUserLevel" value="{{ $userLevel }}">
                        <input type="hidden" id="currentUserCabangId" value="{{ $userCabangId }}">
        
                        <div id="error-messages-supervisor" class="alert alert-danger" style="display: none;"></div>
        
                        <div class="row">
                            {{-- Kolom User --}}
                            <div class="col-md-6 mb-3">
                                <label for="user_id_add" class="form-label">Pilih Supervisor (User) <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id_add" class="form-select" required>
                                    {{-- ... options ... --}}
                                     <option value="" selected disabled>-- Pilih User Supervisor --</option>
                                     @foreach ($availableUsers as $user)
                                         <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                     @endforeach
                                </select>
                                <input type="text" id="user_id_display" class="form-control" disabled style="display: none;">
                                <div class="form-text" id="user-help-text">Hanya user dengan level 'supervisor' yang belum ditugaskan.</div>
                            </div>
        
                            {{-- Kolom Cabang - Logika Kondisional --}}
                            <div class="col-md-6 mb-3" id="cabangFieldContainerSupervisor">
                                <label for="cabang_id_select" class="form-label">
                                     {{-- ... Label Kondisional ... --}}
                                     @if($userLevel === 'admin')
                                         Pilih Cabang <span class="text-danger">*</span>
                                     @else
                                         Cabang Ditugaskan
                                     @endif
                                </label>
                                {{-- ... Select, Text Display, Hidden Input ... --}}
                                {{-- 1. Select (Untuk Admin) --}}
                                 <select name="{{ $userLevel === 'admin' ? 'cabang_id' : '' }}" id="cabang_id_select" class="form-select" {{ $userLevel === 'admin' ? 'required' : '' }} style="{{ $userLevel !== 'admin' ? 'display: none;' : '' }}" >
                                     <option value="" selected disabled>-- Pilih Cabang --</option>
                                      @foreach ($cabangs as $cabang)
                                         <option value="{{ $cabang->id }}">{{ $cabang->nama_perusahaan }} ({{ $cabang->kode_cabang }})</option>
                                     @endforeach
                                 </select>
                                 {{-- 2. Text Display (Untuk Non-Admin View) --}}
                                 <input type="text" id="cabang_id_display_text" class="form-control" value="{{ $userCabangDetail ? $userCabangDetail->nama_perusahaan.' ('.$userCabangDetail->kode_cabang.')' : 'Cabang tidak ditemukan' }}" style="{{ $userLevel === 'admin' ? 'display: none;' : '' }}" disabled>
                                 {{-- 3. Hidden Input (Untuk Non-Admin Submit) --}}
                                 <input type="hidden" name="{{ $userLevel !== 'admin' ? 'cabang_id' : '' }}" id="cabang_id_hidden" value="{{ $userCabangId }}">
                            </div>
        
                            {{-- Kolom Tugas --}}
                            <div class="col-md-12 mb-3">
                                <label for="tugas" class="form-label">Deskripsi Tugas</label>
                                <textarea name="tugas" id="tugas" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
        
                        {{-- Tombol Aksi Form --}}
                        <div class="pt-3 border-top mt-3">
                            <button type="submit" class="btn btn-primary" id="btnSimpansupervisor">Simpan Penugasan</button>
                            <button type="button" class="btn btn-danger" id="btnConfirmDeletesupervisor" style="display: none;">Konfirmasi Hapus Tugas Ini</button>
                            <button type="button" class="btn btn-secondary" id="btnCancelsupervisor" style="display: none;">Batal / Kembali</button>
                        </div>
                    </form>
                </div>
            </div>
        
             {{-- Filter Cabang (Hanya Admin) --}}
             @if($userLevel === 'admin')
             <div class="card mb-3">
                 {{-- ... Filter form ... --}}
                 <div class="card-body">
                      <form method="GET" action="{{ route('supervisor.index') }}" id="filterFormSupervisor">
                          <div class="row g-2">
                              <div class="col-md-4">
                                  <label for="filter_cabang_id" class="form-label">Filter Berdasarkan Cabang:</label>
                                  <select name="cabang_id" id="filter_cabang_id" class="form-select">
                                      <option value="">-- Tampilkan Semua Cabang --</option>
                                      @foreach ($cabangs as $cabang)
                                          <option value="{{ $cabang->id }}" {{ $selectedCabangId == $cabang->id ? 'selected' : '' }}>
                                              {{ $cabang->nama_perusahaan }} ({{ $cabang->kode_cabang }})
                                          </option>
                                      @endforeach
                                  </select>
                              </div>
                              <div class="col-md-2 d-flex align-items-end">
                                  <button type="submit" class="btn btn-info">Filter</button>
                                   @if($selectedCabangId)
                                   <a href="{{ route('supervisor.index') }}" class="btn btn-secondary ms-2">Reset</a>
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
                   <span> Daftar Penugasan Supervisor
                         {{-- ... Konteks Cabang ... --}}
                         @if($userLevel !== 'admin' && $userCabangDetail)
                             - Cabang: <strong>{{ $userCabangDetail->nama_perusahaan }}</strong>
                         @elseif($userLevel === 'admin' && $selectedCabangId && ($filteredCabang = $cabangs->firstWhere('id', $selectedCabangId)))
                             - Filtered: <strong>{{ $filteredCabang->nama_perusahaan }}</strong>
                         @elseif($userLevel === 'admin' && !$selectedCabangId)
                              - (Semua Cabang)
                         @endif
                    </span>
                     {{-- <button id="btnPrintReport" class="btn btn-sm btn-outline-secondary">Cetak Laporan</button> --}}
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="table-dark">
                                {{-- ... Header Tabel ... --}}
                                 <tr>
                                     <th>No</th>
                                     <th>Nama Supervisor</th>
                                     <th>Email</th>
                                     <th>Cabang Ditugaskan</th>
                                     <th>Tugas</th>
                                     <th>Tgl Dibuat</th>
                                     <th>Aksi</th>
                                 </tr>
                            </thead>
                            <tbody id="isisupervisor">
                                {{-- Data awal --}}
                                @include('supervisor.tbody', ['assignments' => $assignments])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- Hapus penutup </body> & </html> dari sini jika ada di layout --}}
        @endsection
        
        @push('js')
        {{-- Hapus <head>, <body>, <html> dari script --}}
        <script>
        $(document).ready(function () {
            console.log("Document Ready! Script Manajemen Supervisor berjalan.");
        
            // --- Konstanta & Variabel --- (Sama seperti sebelumnya)
            const formsupervisor = $('#formsupervisor');
            const formCardsupervisor = $('#supervisorFormCard');
            const formTitlesupervisor = $('#formTitlesupervisor');
            const assignmentIdField = $('#assignmentId');
            const formModesupervisorField = $('#formModesupervisor');
            const errorMessagessupervisor = $('#error-messages-supervisor');
            const btnSimpansupervisor = $('#btnSimpansupervisor');
            const btnCancelsupervisor = $('#btnCancelsupervisor');
            const btnConfirmDeletesupervisor = $('#btnConfirmDeletesupervisor');
            const selectUserIdAdd = $('#user_id_add');
            const inputUserIdDisplay = $('#user_id_display');
            const selectCabangId = $('#cabang_id_select');
            const inputCabangDisplay = $('#cabang_id_display_text');
            const inputCabangHidden = $('#cabang_id_hidden');
            const cabangFieldContainer = $('#cabangFieldContainerSupervisor');
            const inputTugas = $('#tugas');
            const userHelpText = $('#user-help-text');
            const filterCabangSelect = $('#filter_cabang_id');
        
            const currentUserLevel = $('#currentUserLevel').val();
            const currentUserCabangId = $('#currentUserCabangId').val();
        
            // --- Fungsi Helper --- (Sama seperti sebelumnya)
            function setFormStatesupervisor(mode = 'tambah', assignmentData = null) { /* ... Kode setFormStatesupervisor yang sudah diperbaiki ... */
                 formsupervisor[0].reset();
                 errorMessagessupervisor.hide().html('');
                 formModesupervisorField.val(mode);
                 assignmentIdField.val(assignmentData ? assignmentData.id : '');
        
                 const cabangSelect = $('#cabang_id_select');
                 const cabangDisplayText = $('#cabang_id_display_text');
                 const cabangHidden = $('#cabang_id_hidden');
                 const cabangLabel = $('#cabangFieldContainerSupervisor label');
        
                 $('#formsupervisor input:not([type=hidden], [type=button], #cabang_id_display_text), #formsupervisor select, #formsupervisor textarea')
                     .prop('disabled', true);
        
                 selectUserIdAdd.show();
                 inputUserIdDisplay.hide().val('');
                 userHelpText.show();
        
                 if (currentUserLevel === 'admin') {
                     cabangLabel.html('Pilih Cabang <span class="text-danger">*</span>');
                     cabangSelect.show().attr('name', 'cabang_id');
                     cabangDisplayText.hide();
                     cabangHidden.removeAttr('name');
                 } else {
                     cabangLabel.text('Cabang Ditugaskan');
                     cabangSelect.hide().removeAttr('name');
                     cabangDisplayText.show();
                     cabangHidden.val(currentUserCabangId).attr('name', 'cabang_id');
                     let defaultCabangText = "{{ $userCabangDetail ? addslashes($userCabangDetail->nama_perusahaan).' ('.addslashes($userCabangDetail->kode_cabang).')' : 'Cabang tidak ditemukan' }}";
                     cabangDisplayText.val(defaultCabangText);
                 }
        
                 if (mode === 'tambah') {
                     formTitlesupervisor.text('Tambah Penugasan Baru');
                     selectUserIdAdd.prop('disabled', false);
                     inputTugas.prop('disabled', false);
                     if (currentUserLevel === 'admin') {
                         cabangSelect.prop('disabled', false).val('');
                     }
                     btnSimpansupervisor.text('Simpan Penugasan').show().prop('disabled', false);
                     btnCancelsupervisor.hide();
                     btnConfirmDeletesupervisor.hide();
                 } else if (mode === 'edit') {
                     if (!assignmentData || !assignmentData.user || !assignmentData.cabang) {
                         console.error("Data assignment tidak lengkap untuk edit:", assignmentData);
                         Swal.fire('Error', 'Data penugasan tidak lengkap.', 'error');
                         setFormStatesupervisor('tambah');
                         return;
                     }
                     formTitlesupervisor.text('Edit Penugasan: ' + assignmentData.user.name);
                     populateFormsupervisor(assignmentData);
                     selectUserIdAdd.hide().prop('disabled', true);
                     inputUserIdDisplay.show().prop('disabled', true);
                     userHelpText.hide();
                     inputTugas.prop('disabled', false);
                     if (currentUserLevel === 'admin') {
                         cabangSelect.show().prop('disabled', false);
                         cabangDisplayText.hide();
                     } else {
                         cabangSelect.hide();
                         cabangDisplayText.show().prop('disabled', true);
                         let currentCabangText = assignmentData.cabang ? `${assignmentData.cabang.nama_perusahaan} (${assignmentData.cabang.kode_cabang || 'Kode?'})` : 'Cabang Tidak Ditemukan';
                         cabangDisplayText.val(currentCabangText);
                         cabangHidden.val(assignmentData.cabang_id);
                     }
                     btnSimpansupervisor.text('Update Penugasan').show().prop('disabled', false);
                     btnCancelsupervisor.show();
                     btnConfirmDeletesupervisor.hide();
                 } else if (mode === 'delete') {
                     if (!assignmentData || !assignmentData.user || !assignmentData.cabang) {
                         console.error("Data assignment tidak lengkap untuk delete:", assignmentData);
                         Swal.fire('Error', 'Data penugasan tidak lengkap.', 'error');
                         setFormStatesupervisor('tambah');
                         return;
                     }
                     formTitlesupervisor.text('Hapus Penugasan: ' + assignmentData.user.name);
                     populateFormsupervisor(assignmentData);
                     $('#formsupervisor input:not([type=hidden], [type=button]), #formsupervisor select, #formsupervisor textarea')
                         .not('#btnCancelsupervisor, #btnConfirmDeletesupervisor')
                         .prop('disabled', true);
                     selectUserIdAdd.hide().prop('disabled', true);
                     inputUserIdDisplay.show().prop('disabled', true);
                     userHelpText.hide();
                     if (currentUserLevel === 'admin') {
                         cabangSelect.show().prop('disabled', true);
                         cabangDisplayText.hide();
                     } else {
                         cabangSelect.hide();
                         cabangDisplayText.show().prop('disabled', true);
                         let currentCabangText = assignmentData.cabang ? `${assignmentData.cabang.nama_perusahaan} (${assignmentData.cabang.kode_cabang || 'Kode?'})` : 'Cabang Tidak Ditemukan';
                         cabangDisplayText.val(currentCabangText);
                     }
                     btnSimpansupervisor.hide();
                     btnCancelsupervisor.show();
                     btnConfirmDeletesupervisor.show().prop('disabled', false);
                 }
                 $('html, body').animate({ scrollTop: formCardsupervisor.offset().top - 70 }, 300);
             }
        
            function populateFormsupervisor(assignment) { /* ... Kode populateFormsupervisor ... */
                console.log("Populating supervisor form with assignment:", assignment);
                inputTugas.val(assignment.tugas || '');
                if (currentUserLevel === 'admin') {
                     $('#cabang_id_select').val(assignment.cabang_id);
                 }
                 if (assignment.user) {
                     $('#user_id_display').val(`${assignment.user.name || 'Nama?'} (${assignment.user.email || 'Email?'})`);
                 } else {
                     console.warn("Assignment object is missing 'user' details in populateFormsupervisor:", assignment);
                     $('#user_id_display').val('User Tidak Ditemukan');
                 }
            }
        
            function displayErrorssupervisor(errors) { /* ... Kode displayErrorssupervisor ... */
                let errorHtml = '<ul>';
                 $.each(errors, function(key, value) {
                     let fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                     errorHtml += `<li>${value[0]}</li>`;
                 });
                 errorHtml += '</ul>';
                 errorMessagessupervisor.html(errorHtml).show();
            }
        
            function refreshsupervisorTable(cabangIdFilter = null) { /* ... Kode refreshsupervisorTable ... */
                 console.log("Attempting to refresh Supervisor table... Filter:", cabangIdFilter);
                 $('#isisupervisor').html('<tr><td colspan="7" class="text-center">Memuat data...</td></tr>');
                 let url = "{{ route('supervisor.data') }}";
                 let data = {};
                 if (cabangIdFilter) {
                     data.cabang_id = cabangIdFilter;
                 }
                 $.ajax({
                     url: url, type: 'GET', data: data,
                     success: function(data) {
                         console.log("AJAX success for refreshsupervisorTable.");
                         $('#isisupervisor').html(data);
                     },
                     error: function(xhr) {
                         console.error("AJAX error in refreshsupervisorTable:", xhr);
                         let errorMsg = 'Gagal memuat data.';
                         if(xhr.status >= 500) errorMsg += ' Kesalahan Server.';
                         else if(xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                         $('#isisupervisor').html(`<tr><td colspan="7" class="text-center text-danger">${errorMsg}</td></tr>`);
                     }
                 });
            }
        
            // --- Event Listeners --- (Sama seperti sebelumnya)
        
            // Edit Button
            $(document).on('click', '.btn-edit-supervisor', function() { /* ... Kode edit button handler ... */
                const button = $(this);
                 console.log("Edit Supervisor - Button Clicked");
                 let rawDataAttr = button.attr('data-assignment');
                 let decodedJsonString;
                 try {
                     if (!rawDataAttr) throw new Error("Atribut data-assignment kosong.");
                     console.log("Edit Supervisor - Raw data-assignment:", rawDataAttr);
                     const parser = new DOMParser();
                     const dom = parser.parseFromString('<!doctype html><body>' + rawDataAttr, 'text/html');
                     decodedJsonString = dom.body.textContent;
                     console.log("Edit Supervisor - Decoded JSON string:", decodedJsonString);
                     if (!decodedJsonString) throw new Error("String JSON kosong setelah decode.");
                     let assignment = JSON.parse(decodedJsonString);
                     console.log("Edit Supervisor - Parsed Object:", assignment);
                     if(assignment && assignment.id && assignment.user && assignment.user.name && assignment.cabang && assignment.cabang.nama_perusahaan) {
                         setFormStatesupervisor('edit', assignment);
                     } else {
                         throw new Error("Data assignment tidak lengkap/valid setelah parsing.");
                     }
                 } catch(e) {
                     console.error("Error processing data-assignment for Edit:", e);
                     console.error("Raw attribute value during error:", rawDataAttr || 'N/A');
                     if (typeof decodedJsonString !== 'undefined') { console.error("Decoded string value during error:", decodedJsonString); }
                     Swal.fire('Error', `Gagal memproses data: ${e.message}. Periksa console.`, 'error');
                 }
            });
        
            // Hapus Button
            $(document).on('click', '.btn-hapus-supervisor', function() { /* ... Kode hapus button handler ... */
                const button = $(this);
                 let assignmentIdFromData = button.data('id');
                 console.log("Delete Supervisor - Button Clicked, data-id:", assignmentIdFromData);
                 let rawDataAttr = button.attr('data-assignment');
                 let decodedJsonString;
                 try {
                     if (!rawDataAttr) throw new Error("Atribut data-assignment kosong.");
                     console.log("Delete Supervisor - Raw data-assignment:", rawDataAttr);
                     const parser = new DOMParser();
                     const dom = parser.parseFromString('<!doctype html><body>' + rawDataAttr, 'text/html');
                     decodedJsonString = dom.body.textContent;
                     console.log("Delete Supervisor - Decoded JSON string:", decodedJsonString);
                     if (!decodedJsonString) throw new Error("String JSON kosong setelah decode.");
                     let assignment = JSON.parse(decodedJsonString);
                     console.log("Delete Supervisor - Parsed Object:", assignment);
                     let finalAssignmentId = assignment ? assignment.id : assignmentIdFromData;
                     console.log("Delete Supervisor - Final Assignment ID:", finalAssignmentId);
                     if (finalAssignmentId && assignment && assignment.user && assignment.user.name && assignment.cabang && assignment.cabang.nama_perusahaan) {
                         assignmentIdField.val(finalAssignmentId);
                         setFormStatesupervisor('delete', assignment);
                     } else {
                         if(finalAssignmentId){
                             console.warn("Incomplete data after parse, attempting minimal delete setup.");
                             assignmentIdField.val(finalAssignmentId);
                             setFormStatesupervisor('delete', { id: finalAssignmentId, user: { name: 'Data Tidak Lengkap' }, cabang: { nama_perusahaan: 'N/A'} });
                             $('#user_id_display').val('Data User Tidak Lengkap');
                             Swal.fire('Peringatan', 'Data penugasan tidak lengkap, namun ID ditemukan. Konfirmasi penghapusan?', 'warning');
                         } else { throw new Error("Data assignment tidak lengkap/valid atau ID tidak ditemukan."); }
                     }
                 } catch(e) {
                     console.error("Error processing data-assignment for Delete:", e);
                     console.error("Raw attribute value during error:", rawDataAttr || 'N/A');
                     if (typeof decodedJsonString !== 'undefined') { console.error("Decoded string value during error:", decodedJsonString); }
                     Swal.fire('Error', `Gagal memproses data: ${e.message}. Periksa console.`, 'error');
                 }
            });
        
            // Cancel Button
            $('#btnCancelsupervisor').click(function() { setFormStatesupervisor('tambah'); });
        
            // Form Submit (Tambah / Update) --- PERBAIKAN DI SINI ---
            formsupervisor.submit(function (e) {
                e.preventDefault();
                // HAPUS CEK LEVEL ADMIN DARI SINI
                // if (currentUserLevel !== 'admin') {
                //     console.warn("Non-admin tried to submit supervisor form.");
                //     return;
                // }
        
                btnSimpansupervisor.prop('disabled', true).text('Menyimpan...');
                errorMessagessupervisor.hide().html('');
        
                let mode = formModesupervisorField.val();
                let assignmentId = assignmentIdField.val();
                let url = '';
                let formData = new FormData(this);
                let method = 'POST';
        
                if (mode === 'edit') {
                    formData.delete('user_id'); // Jangan kirim user_id dari form saat update
                    console.log('Updating assignment ID:', assignmentId);
                    let urlTemplate = "{{ route('supervisor.update', ['superviso' => ':id']) }}";
                    url = urlTemplate.replace(':id', assignmentId);
                    formData.append('_method', 'PUT');
                } else if(mode === 'tambah') {
                    console.log('Storing new assignment.');
                    url = "{{ route('supervisor.store') }}";
                }
                else {
                    btnSimpansupervisor.prop('disabled', false).text('Aksi Tidak Valid');
                    return;
                }
        
                console.log("Submitting form data for mode:", mode, "to URL:", url);
                // for (let [key, value] of formData.entries()) { console.log(key, value); } // Keep for debugging if needed
        
                $.ajax({
                    url: url, type: method, data: formData, processData: false, contentType: false,
                    success: function (response) {
                        if (response.success) {
                            let currentFilter = (currentUserLevel === 'admin') ? filterCabangSelect.val() : null;
                            refreshsupervisorTable(currentFilter); // Refresh dengan filter (jika admin)
                            setFormStatesupervisor('tambah');
                            Swal.fire('Berhasil!', response.message, 'success');
                        } else {
                            Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                            btnSimpansupervisor.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Penugasan' : 'Update Penugasan');
                        }
                    },
                    error: function (xhr) {
                        btnSimpansupervisor.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Penugasan' : 'Update Penugasan');
                        if (xhr.status === 422) {
                            displayErrorssupervisor(xhr.responseJSON.errors);
                            Swal.fire('Error Validasi', 'Periksa kembali isian form Anda.', 'error');
                        } else if (xhr.status === 403) { // Tangani error 403 dari controller
                            Swal.fire('Akses Ditolak!', xhr.responseJSON.message || 'Anda tidak memiliki izin untuk melakukan aksi ini.', 'error');
                        } else {
                            console.error("AJAX Error:", xhr);
                            Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Terjadi kesalahan pada server.'), 'error');
                        }
                    }
                });
            });
        
            // Confirm Delete Button in Form --- PERBAIKAN DI SINI ---
            $('#btnConfirmDeletesupervisor').click(function() {
                 // HAPUS CEK LEVEL ADMIN DARI SINI
                // if (currentUserLevel !== 'admin') {
                //      console.warn("Non-admin tried to confirm delete.");
                //      return;
                //  }
        
                let id = assignmentIdField.val();
                let supervisorName = inputUserIdDisplay.val() || 'Supervisor ini (Nama Tidak Ditemukan)';
                console.log("Confirm delete clicked. Assignment ID:", id);
                if (!id) { Swal.fire('Error', 'ID Penugasan tidak ditemukan.', 'error'); return; }
        
                Swal.fire({
                    title: 'Anda Yakin?',
                    html: `Anda akan menghapus penugasan untuk:<br><strong>${supervisorName}</strong>?`,
                    icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({ title: 'Menghapus...', html: 'Mohon tunggu sebentar...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
                        let urlTemplate = "{{ route('supervisor.destroy', ['superviso' => ':id']) }}";
                        let url = urlTemplate.replace(':id', id);
                        console.log("Sending DELETE request to:", url);
        
                        $.ajax({
                            url: url, type: 'POST',
                            data: { _method: 'DELETE', _token: "{{ csrf_token() }}" },
                            success: function (response) {
                                if (response.success) {
                                    let currentFilter = (currentUserLevel === 'admin') ? filterCabangSelect.val() : null;
                                    refreshsupervisorTable(currentFilter); // Refresh dengan filter (jika admin)
                                    setFormStatesupervisor('tambah');
                                    Swal.fire('Dihapus!', response.message, 'success');
                                } else { Swal.fire('Gagal!', response.message || 'Gagal menghapus.', 'error'); }
                            },
                            error: function (xhr) {
                                console.error("AJAX Delete Error:", xhr);
                                if (xhr.status === 403) { // Tangani error 403 dari controller
                                    Swal.fire('Akses Ditolak!', xhr.responseJSON.message || 'Anda tidak memiliki izin untuk menghapus data ini.', 'error');
                                } else {
                                    Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Gagal menghapus data.'), 'error');
                                }
                            }
                        });
                    }
                });
            });
        
            // --- Filter Handling --- (Sama seperti sebelumnya)
        
            // --- Inisialisasi ---
            setFormStatesupervisor('tambah');
        
        });
</script>
</body>
</html>
{{-- Hapus penutup </body> & </html> --}}
@endpush
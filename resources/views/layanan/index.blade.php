@extends('layout.template') {{-- Sesuaikan path layout Anda --}}

@section('title', 'Manajemen Layanan')

@section('content')
<!DOCTYPE html>
<html>
<head>
    {{-- Pastikan CSS/JS Bootstrap & jQuery sudah dimuat dari layout --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container-fluid">
        <h4 class="py-3 mb-4">
            <span class="text-muted fw-light">Master Data /</span> Manajemen Layanan
        </h4>

        {{-- 1. Card Form Inline --}}
        <div class="card mb-4" id="layananFormCard">
            <div class="card-header">
                <h5 class="card-title mb-0" id="formTitle">Tambah Layanan Baru</h5>
            </div>
            <div class="card-body">
                <form id="formLayanan"> {{-- ID Form diubah --}}
                    @csrf
                    <input type="hidden" id="formMode" value="tambah">
                    <input type="hidden" id="layananId"> {{-- ID Field diubah --}}

                    <div id="error-messages" class="alert alert-danger" style="display: none;"></div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_layanan" class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_layanan" id="nama_layanan" class="form-control" required>
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="harga_per_unit" class="form-label">Harga per Unit <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="harga_per_unit" id="harga_per_unit" class="form-control" required>
                         </div>
                         <div class="col-md-4 mb-3"> {{-- Kecilkan kolom --}}
                            <label for="satuan_id" class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan_id" id="satuan_id" class="form-select" required>
                                <option value="">-- Pilih Satuan --</option>
                                @foreach ($satuans as $satuan)
                                    <option value="{{ $satuan->satuan_id }}">{{ $satuan->nama_satuan }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="col-md-4 mb-3"> {{-- Kecilkan kolom --}}
                            <label for="estimasi_durasi_hari" class="form-label">Estimasi Durasi (Hari)</label>
                            <input type="number" name="estimasi_durasi_hari" id="estimasi_durasi_hari" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3"> {{-- Kecilkan kolom --}}
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tombol Aksi Form --}}
                    <div class="pt-3 border-top mt-1">
                        <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan Layanan Baru</button>
                        <button type="button" class="btn btn-danger" id="btnConfirmDelete" style="display: none;">Konfirmasi Hapus Layanan Ini</button>
                        <button type="button" class="btn btn-secondary" id="btnCancel" style="display: none;">Batal / Kembali ke Tambah</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 2. Card Tabel Layanan --}}
        <div class="card">
            <div class="card-header">Daftar Layanan</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Layanan</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Satuan</th>
                                <th scope="col">Estimasi (Hari)</th>
                                <th scope="col">Status</th>
                                <th scope="col">Dibuat</th>
                                <th scope="col">Diupdate</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="isiLayanan"> {{-- ID tbody diubah --}}
                            @include('layanan.tbody', ['layanans' => $layanans]) {{-- Path include diubah --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        console.log("Document Ready! Script Manajemen Layanan (Inline Form) berjalan.");

        // --- Konstanta & Variabel ---
        const formLayanan = $('#formLayanan'); // Ubah ID
        const formCard = $('#layananFormCard'); // Ubah ID
        const formTitle = $('#formTitle');
        const layananIdField = $('#layananId'); // Ubah ID
        const formModeField = $('#formMode');
        const errorMessages = $('#error-messages');
        const btnSimpan = $('#btnSimpan');
        const btnCancel = $('#btnCancel');
        const btnConfirmDelete = $('#btnConfirmDelete');

        // Target inputs/selects
        const formInputsSelector = '#formLayanan input, #formLayanan select'; // Ubah ID form
        const exceptionsSelector = '[type=hidden], #btnCancel, #btnConfirmDelete, #btnSimpan';

        // Fungsi state form
        function setFormState(mode = 'tambah', layananData = null) {
            formLayanan[0].reset(); // Ubah ID
            errorMessages.hide().html('');
            formModeField.val(mode);
            layananIdField.val(layananData ? layananData.layanan_id : ''); // Ubah properti ID

            $(formInputsSelector).not(exceptionsSelector).prop('disabled', false);

            if (mode === 'tambah') {
                formTitle.text('Tambah Layanan Baru');
                btnSimpan.text('Simpan Layanan Baru').show().prop('disabled', false);
                btnCancel.hide();
                btnConfirmDelete.hide();
                $('#status').val('1'); // Default status Aktif saat tambah
            } else if (mode === 'edit') {
                if (!layananData) return;
                formTitle.text('Edit Layanan: ' + layananData.nama_layanan);
                populateForm(layananData);
                btnSimpan.text('Update Layanan').show().prop('disabled', false);
                btnCancel.show();
                btnConfirmDelete.hide();
            } else if (mode === 'delete') {
                if (!layananData) return;
                formTitle.text('Hapus Layanan (Konfirmasi): ' + layananData.nama_layanan);
                populateForm(layananData);
                $(formInputsSelector).not(exceptionsSelector).prop('disabled', true);
                btnSimpan.hide().prop('disabled', true);
                btnCancel.show();
                btnConfirmDelete.show().prop('disabled', false);
            }

            $('html, body').animate({scrollTop: formCard.offset().top - 70}, 300);
        }

        // Fungsi isi form
        function populateForm(layanan) {
            $('#nama_layanan').val(layanan.nama_layanan);
            $('#harga_per_unit').val(layanan.harga_per_unit);
            $('#satuan_id').val(layanan.satuan_id);
            $('#estimasi_durasi_hari').val(layanan.estimasi_durasi_hari);
            $('#status').val(layanan.status ? '1' : '0'); // Konversi boolean ke 1/0
        }

        // Fungsi display error
        function displayErrors(errors) {
            let errorHtml = '<ul>';
            $.each(errors, function(key, value) {
                errorHtml += '<li>' + value[0] + '</li>';
            });
            errorHtml += '</ul>';
            errorMessages.html(errorHtml).show();
        }

        // Fungsi refresh tabel
        function refreshLayananTable() {
            $('#isiLayanan').html('<tr><td colspan="9" class="text-center">Memuat data...</td></tr>'); // Ubah colspan
            $.ajax({
                url: "{{ route('layanan.data') }}", // Ubah route name
                type: 'GET',
                success: function(data) {
                    $('#isiLayanan').html(data); // Ubah ID target
                },
                error: function(xhr) {
                    console.error("Gagal memuat data tabel:", xhr);
                    $('#isiLayanan').html( // Ubah ID target
                        '<tr><td colspan="9" class="text-center text-danger">Gagal memuat data. Silakan coba lagi.</td></tr>' // Ubah colspan
                    );
                }
            });
        }


        // --- Event Listeners ---

        // Tombol Edit
        $(document).on('click', '.btn-edit', function() {
            let layananJson = $(this).data('layanan'); // Ubah nama data attribute
            let layanan = (typeof layananJson === 'string') ? JSON.parse(layananJson) : layananJson;
            console.log("Edit clicked", layanan);
            setFormState('edit', layanan);
        });

        // Tombol Hapus
        $(document).on('click', '.btn-hapus', function() {
            let layananJson = $(this).closest('tr').find('.btn-edit').data('layanan'); // Ubah nama data attribute
            let layanan = (typeof layananJson === 'string') ? JSON.parse(layananJson) : layananJson;
            console.log("Delete view clicked", layanan);
            layananIdField.val(layanan.layanan_id); // Ubah properti ID
            setFormState('delete', layanan);
        });

        // Tombol Batal
        $('#btnCancel').click(function() {
            console.log("Cancel clicked");
            setFormState('tambah');
        });

        // Form Submit
        formLayanan.submit(function(e) { // Ubah ID form
            e.preventDefault();
            btnSimpan.prop('disabled', true).text('Menyimpan...');
            errorMessages.hide().html('');

            let mode = formModeField.val();
            let layananId = layananIdField.val(); // Ubah ID field
            let url = '';
            let formData = new FormData(this);
            let method = 'POST';

            if (mode === 'tambah') {
                url = "{{ route('layanan.store') }}"; // Ubah route name
            } else if (mode === 'edit') {
                 // Ubah route name dan parameter
                let urlTemplate = "{{ route('layanan.update', ['layanan' => ':id']) }}";
                url = urlTemplate.replace(':id', layananId);
                formData.append('_method', 'PUT');
            } else {
                btnSimpan.prop('disabled', false).text('Simpan');
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
                        refreshLayananTable(); // Ubah nama fungsi
                        setFormState('tambah');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                        btnSimpan.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Layanan Baru' : 'Update Layanan');
                    }
                },
                error: function(xhr) {
                    btnSimpan.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Layanan Baru' : 'Update Layanan');
                    if (xhr.status === 422) {
                        displayErrors(xhr.responseJSON.errors);
                        Swal.fire({
                            title: 'Error Validasi',
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

        // Tombol Konfirmasi Hapus
        $('#btnConfirmDelete').click(function() {
            let id = layananIdField.val(); // Ubah ID field
            let namaLayanan = $('#nama_layanan').val(); // Ubah ID field

            if (!id) {
                Swal.fire('Error', 'ID Layanan tidak ditemukan.', 'error');
                return;
            }
            console.log("Confirm delete clicked for ID:", id);

            Swal.fire({
                title: 'Anda Yakin?',
                 html: `Anda akan menghapus layanan: <strong>${namaLayanan}</strong>.<br>Tindakan ini tidak bisa dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("User confirmed deletion for ID:", id);
                    Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                     // Ubah route name dan parameter
                    let urlTemplate = "{{ route('layanan.destroy', ['layanan' => ':id']) }}";
                    let url = urlTemplate.replace(':id', id);

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: { _method: 'DELETE', _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            if (response.success) {
                                refreshLayananTable(); // Ubah nama fungsi
                                setFormState('tambah');
                                Swal.fire('Dihapus!', response.message, 'success');
                            } else {
                                Swal.fire('Gagal!', response.message || 'Gagal menghapus data.', 'error');
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            let errorMsg = 'Tidak dapat terhubung ke server.';
                            if(xhr.responseJSON && xhr.responseJSON.message){ errorMsg = xhr.responseJSON.message; }
                            else if (xhr.statusText) { errorMsg = xhr.statusText; }
                            Swal.fire('Error ' + xhr.status, errorMsg, 'error');
                        }
                    });
                } else {
                    console.log("User cancelled deletion.");
                }
            });
        });

        // --- Inisialisasi ---
        setFormState('tambah');

    });
</script>
@endpush
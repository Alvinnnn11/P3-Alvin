@extends('layout.template')
@section('title', 'Manajemen Customer')

{{-- Hapus struktur HTML dasar jika tercopy --}}

@section('content')
<div class="container">
    <h4 class="my-3">Manajemen Customer</h4>

    {{-- Card Form Inline --}}
    <div class="card mb-4" id="customerFormCard">
        <div class="card-header"><h5 class="card-title mb-0" id="formTitleCustomer">Tambah Customer Baru</h5></div>
        <div class="card-body">
            <form id="formCustomer">
                @csrf
                <input type="hidden" id="formModeCustomer" value="tambah">
                <input type="hidden" id="customerId"> {{-- ID record customer --}}

                <div id="error-messages-customer" class="alert alert-danger" style="display: none;"></div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="user_id_add" class="form-label">Pilih User <span class="text-danger">*</span></label>
                        {{-- Dropdown untuk Tambah --}}
                        <select name="user_id" id="user_id_add" class="form-select" required>
                            <option value="" selected disabled>-- Pilih User --</option>
                            @forelse ($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @empty
                                <option value="" disabled>-- Semua user sudah menjadi customer --</option>
                            @endforelse
                        </select>
                        {{-- Input Disabled untuk Edit/Delete View --}}
                        <input type="text" id="user_id_display" class="form-control" disabled style="display: none;">
                        <div class="form-text" id="user-help-text">Pilih user yang akan dijadikan customer.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="saldo" class="form-label">Saldo Awal / Saldo <span class="text-danger">*</span></label>
                        <input type="number" name="saldo" id="saldo" class="form-control" required min="0" step="any" value="0"> {{-- step="any" untuk desimal --}}
                    </div>
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="pt-3 border-top mt-3">
                    <button type="submit" class="btn btn-primary" id="btnSimpanCustomer">Simpan Customer</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDeleteCustomer" style="display: none;">Konfirmasi Hapus Customer Ini</button>
                    <button type="button" class="btn btn-secondary" id="btnCancelCustomer" style="display: none;">Batal / Kembali</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Card Tabel Customer --}}
    <div class="card">
        <div class="card-header"> Daftar Customer </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Customer</th>
                            <th>Email</th>
                            <th>Saldo</th>
                            <th>Tgl Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isiCustomer">
                        @include('customers.tbody', ['customers' => $customers])
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    {{-- Tambahkan script AJAX CRUD Customer di sini --}}
    <script>
    $(document).ready(function () {
        console.log("Document Ready! Script Manajemen Customer berjalan.");

        // --- Konstanta & Variabel ---
        const formCustomer = $('#formCustomer');
        const formCardCustomer = $('#customerFormCard');
        const formTitleCustomer = $('#formTitleCustomer');
        const customerIdField = $('#customerId');
        const formModeCustomerField = $('#formModeCustomer');
        const errorMessagesCustomer = $('#error-messages-customer');
        const btnSimpanCustomer = $('#btnSimpanCustomer');
        const btnCancelCustomer = $('#btnCancelCustomer');
        const btnConfirmDeleteCustomer = $('#btnConfirmDeleteCustomer');
        const selectUserIdAdd = $('#user_id_add'); // Dropdown untuk Tambah
        const inputUserIdDisplay = $('#user_id_display'); // Input display untuk Edit/Delete
        const inputSaldo = $('#saldo');
        const userHelpText = $('#user-help-text');

        const formInputsSelectorCustomer = '#formCustomer select, #formCustomer input[type=number]';
        const exceptionsSelectorCustomer = '#user_id_display, [type=hidden], #btnCancelCustomer, #btnConfirmDeleteCustomer, #btnSimpanCustomer';

        // --- Fungsi Helper ---

        function setFormStateCustomer(mode = 'tambah', customerData = null) {
            formCustomer[0].reset();
            errorMessagesCustomer.hide().html('');
            formModeCustomerField.val(mode);
            customerIdField.val(customerData ? customerData.id : '');

            // Reset & Enable inputs
            $(formInputsSelectorCustomer).not(exceptionsSelectorCustomer).prop('disabled', false);
            selectUserIdAdd.prop('disabled', false).show();
            inputUserIdDisplay.hide().val('');
            userHelpText.text('Pilih user yang akan dijadikan customer.').show();
            inputSaldo.val('0'); // Default saldo 0 saat tambah

            if (mode === 'tambah') {
                formTitleCustomer.text('Tambah Customer Baru');
                btnSimpanCustomer.text('Simpan Customer Baru').show().prop('disabled', false);
                btnCancelCustomer.hide(); btnConfirmDeleteCustomer.hide();
            } else if (mode === 'edit') {
                if (!customerData || !customerData.user) return;
                formTitleCustomer.text('Edit Customer: ' + customerData.user.name);
                populateFormCustomer(customerData);

                // User tidak bisa diubah saat edit
                selectUserIdAdd.hide().prop('disabled', true);
                inputUserIdDisplay.val(customerData.user.name + ' (' + customerData.user.email + ')').show();
                userHelpText.text('User tidak dapat diubah.').show();

                inputSaldo.prop('disabled', false); // Saldo bisa diedit

                btnSimpanCustomer.text('Update Customer').show().prop('disabled', false);
                btnCancelCustomer.show(); btnConfirmDeleteCustomer.hide();
            } else if (mode === 'delete') {
                if (!customerData || !customerData.user) return;
                formTitleCustomer.text('Hapus Customer (Konfirmasi): ' + customerData.user.name);
                populateFormCustomer(customerData);

                // Disable semua
                $(formInputsSelectorCustomer).not(exceptionsSelectorCustomer).prop('disabled', true);
                selectUserIdAdd.hide().prop('disabled', true);
                inputUserIdDisplay.val(customerData.user.name + ' (' + customerData.user.email + ')').show();
                userHelpText.hide();
                inputSaldo.prop('disabled', true);

                btnSimpanCustomer.hide(); btnCancelCustomer.show(); btnConfirmDeleteCustomer.show().prop('disabled', false);
            }
            $('html, body').animate({ scrollTop: formCardCustomer.offset().top - 70 }, 300);
        }

        function populateFormCustomer(customer) {
            // user_id dihandle di setFormState
            inputSaldo.val(customer.saldo);
        }

        function displayErrorsCustomer(errors) {
            let errorHtml = '<ul>';
            $.each(errors, function(key, value) { errorHtml += `<li>${value[0]}</li>`; });
            errorHtml += '</ul>';
            errorMessagesCustomer.html(errorHtml).show();
        }

        function refreshCustomerTable() {
             console.log("Refreshing Customer table...");
             // Sesuaikan colspan (No, Nama, Email, Saldo, Tgl Daftar, Aksi = 6)
             $('#isiCustomer').html('<tr><td colspan="6" class="text-center">Memuat data...</td></tr>');
             $.ajax({
                 url: "{{ route('customer.data') }}", type: 'GET',
                 success: function(data) { $('#isiCustomer').html(data); },
                 error: function(xhr) {
                     console.error("Gagal memuat data tabel customer:", xhr);
                     $('#isiCustomer').html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat data customer.</td></tr>');
                 }
             });
         }

         // Fungsi untuk reload user di dropdown (opsional)
         function reloadAvailableUsersCustomer() {
             // Implementasi AJAX untuk get user yg belum jadi customer jika perlu
             console.log("Placeholder: Reload available users for customer dropdown.");
         }

        // --- Event Listeners ---

        $(document).on('click', '.btn-edit-customer', function() { // Class tombol edit
            let customerJson = $(this).data('customer');
            console.log("Edit Customer - Raw Data:", customerJson);
             try {
                let customer = (typeof customerJson === 'string') ? JSON.parse(customerJson) : customerJson;
                 console.log("Edit Customer - Parsed Data:", customer);
                 if(customer && customer.id && customer.user) {
                     customerIdField.val(customer.id); // Simpan ID customer
                     setFormStateCustomer('edit', customer);
                 } else { throw new Error('Data customer tidak lengkap.'); }
             } catch(e) { console.error("Error parsing customer data:", e); Swal.fire('Error', 'Gagal membaca data customer.', 'error'); }
        });

        $(document).on('click', '.btn-hapus-customer', function() { // Class tombol hapus
            let customerId = $(this).data('id');
            let customerJson = $(this).data('customer');
            console.log("Delete Customer View - ID:", customerId, "Raw Data:", customerJson);
            try {
                 let customer = (typeof customerJson === 'string') ? JSON.parse(customerJson) : customerJson;
                  console.log("Delete Customer View - Parsed Data:", customer);
                  let finalCustomerId = customer ? customer.id : customerId;
                  if (finalCustomerId && customer && customer.user) {
                     customerIdField.val(finalCustomerId); // Set ID customer ke hidden field
                     setFormStateCustomer('delete', customer);
                 } else { throw new Error('Data customer tidak lengkap untuk dihapus.'); }
            } catch(e) { console.error("Error parsing customer data for delete:", e); Swal.fire('Error', 'Gagal membaca data customer untuk dihapus.', 'error'); }
        });

        $('#btnCancelCustomer').click(function() { setFormStateCustomer('tambah'); });

        // Submit Form Customer (Tambah/Edit)
        formCustomer.submit(function (e) {
            e.preventDefault();
            btnSimpanCustomer.prop('disabled', true).text('Menyimpan...');
            errorMessagesCustomer.hide().html('');

            let mode = formModeCustomerField.val();
            let customerId = customerIdField.val();
            let url = '';
            let formData = new FormData(this);
            let method = 'POST';

            // Saat edit, user_id tidak boleh dikirim karena disabled
            if (mode === 'edit') {
                 formData.delete('user_id'); // Hapus user_id dari data yg dikirim
                 let urlTemplate = "{{ route('customer.update', ['customer' => ':id']) }}"; // Gunakan {customer}
                 url = urlTemplate.replace(':id', customerId);
                 formData.append('_method', 'PUT');
            } else if (mode === 'tambah') {
                 url = "{{ route('customer.store') }}";
                  // Pastikan user_id terpilih
                 if (!selectUserIdAdd.val()) {
                    displayErrorsCustomer({'user_id': ['User wajib dipilih.']});
                    btnSimpanCustomer.prop('disabled', false).text('Simpan Customer Baru');
                    return; // Hentikan submit
                 }
            } else { return; } // Mode delete tidak disubmit

            console.log("Submitting customer data for mode:", mode, "URL:", url);

            $.ajax({
                url: url, type: method, data: formData, processData: false, contentType: false,
                success: function (response) {
                    if (response.success) {
                        refreshCustomerTable();
                        reloadAvailableUsersCustomer(); // Refresh dropdown user
                        setFormStateCustomer('tambah');
                        Swal.fire('Berhasil!', response.message, 'success');
                    } else {
                        Swal.fire('Gagal!', response.message || 'Error server.', 'error');
                        btnSimpanCustomer.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Customer Baru' : 'Update Customer');
                    }
                },
                error: function (xhr) {
                    btnSimpanCustomer.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Customer Baru' : 'Update Customer');
                    if (xhr.status === 422) {
                        displayErrorsCustomer(xhr.responseJSON.errors);
                        Swal.fire('Error Validasi', 'Periksa isian form.', 'error');
                    } else {
                        console.error("AJAX Error:", xhr);
                        Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Error server.'), 'error');
                    }
                }
            });
        });

        // Tombol Konfirmasi Hapus Customer
        $('#btnConfirmDeleteCustomer').click(function() {
            let id = customerIdField.val(); // ID Customer dari hidden field
            let customerName = inputUserIdDisplay.val() || 'Customer ini'; // Nama dari display
            console.log("Confirm delete customer clicked. ID:", id);
            if (!id) { Swal.fire('Error', 'ID Customer tidak ditemukan.', 'error'); return; }

            Swal.fire({
                title: 'Anda Yakin?', html: `Anda akan menghapus data customer untuk: <strong>${customerName}</strong>?<br>Ini hanya menghapus status customernya, bukan data user.`,
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Hapus Customer!', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    let urlTemplate = "{{ route('customer.destroy', ['customer' => ':id']) }}"; // Gunakan {customer}
                    let url = urlTemplate.replace(':id', id);
                    console.log("Sending DELETE request to:", url);

                    $.ajax({
                        url: url, type: 'POST', data: { _method: 'DELETE', _token: "{{ csrf_token() }}" },
                        success: function (response) {
                            if (response.success) {
                                refreshCustomerTable(); reloadAvailableUsersCustomer(); setFormStateCustomer('tambah');
                                Swal.fire('Dihapus!', response.message, 'success');
                            } else { Swal.fire('Gagal!', response.message || 'Error.', 'error'); }
                        },
                        error: function (xhr) { console.error("AJAX Delete Error:", xhr); Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Error.'), 'error'); }
                    });
                }
            });
        });

        // --- Inisialisasi ---
        setFormStateCustomer('tambah');

    });
    </script>
@endpush
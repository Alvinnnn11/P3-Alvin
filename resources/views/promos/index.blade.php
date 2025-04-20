@extends('layout.template') {{-- Sesuaikan dengan path layout Anda --}}
@section('title', 'Manajemen Promo')

@section('content')
<div class="container-fluid"> {{-- Gunakan container-fluid untuk layout lebih lebar --}}
    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
        <h4 class="mb-0">
             <span class="text-muted fw-light">Master Data /</span> Manajemen Promo
        </h4>
        {{-- Tombol untuk Buka/Tutup Form Tambah --}}
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#promoFormCardCollapse" aria-expanded="false" aria-controls="promoFormCardCollapse" id="btnToggleFormPromo">
            <i class="bx bx-plus me-1"></i> <span id="btnToggleText">Tambah Promo</span>
        </button>
    </div>

    {{-- Notifikasi Sukses/Error (dari redirect, jarang dipakai di full AJAX tapi tidak masalah ada) --}}
    @if (session('success')) <div class="alert alert-success alert-dismissible fade show" role="alert"> {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div> @endif
    @if (session('error')) <div class="alert alert-danger alert-dismissible fade show" role="alert"> {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div> @endif

    {{-- Filter Cabang (Hanya untuk Admin) --}}
    @if(Auth::user()->level === 'admin')
    <div class="card mb-3">
        <div class="card-body">
            {{-- Form ini tidak perlu action/method jika filter via AJAX --}}
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label for="filter_cabang" class="form-label">Filter Cabang:</label>
                    <select name="filter_cabang" id="filter_cabang" class="form-select form-select-sm">
                        <option value="all" {{ !$targetCabangId || $targetCabangId == 'all' ? 'selected' : '' }}>-- Semua Cabang --</option>
                        @foreach($cabangsForFilter as $c) {{-- Pastikan $cabangsForFilter dikirim dari controller index() --}}
                        <option value="{{ $c->id }}" {{ $targetCabangId == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_perusahaan }}
                        </option>
                        @endforeach
                    </select>
                </div>
                 {{-- Tombol Filter/Reset tidak diperlukan jika refresh otomatis on change --}}
                 {{-- <div class="col-md-2">
                     <button type="button" class="btn btn-secondary btn-sm w-100" id="btnApplyFilter">Filter</button>
                 </div>
                 <div class="col-md-2">
                     <a href="{{ route('promo.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                 </div> --}}
            </div>
        </div>
    </div>
    @endif
    {{-- Akhir Filter Cabang --}}

    {{-- Card Form Inline (Collapsible) --}}
    <div class="collapse" id="promoFormCardCollapse">
        <div class="card mb-4" id="promoFormCard">
            <div class="card-header"><h5 class="card-title mb-0" id="formTitlePromo">Tambah Promo Baru</h5></div>
            <div class="card-body">
                {{-- Form utama untuk AJAX --}}
                <form id="formPromo">
                    @csrf
                    <input type="hidden" id="formModePromo" value="tambah">
                    <input type="hidden" id="promoId">
                    <div id="error-messages-promo" class="alert alert-danger" style="display: none;"></div>

                    {{-- Include Partial Form --}}
                    {{-- Pastikan $cabangsForFilter dikirim sebagai $cabangs ke partial --}}
                    @include('promos._form', ['promo' => null, 'cabangs' => $cabangsForFilter])

                </form>
            </div>
        </div>
    </div>
    {{-- Akhir Card Form Inline --}}

    {{-- Card Tabel Promo --}}
    <div class="card">
        <div class="card-header">Daftar Promo</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Cabang</th>
                            <th>Member?</th>
                            <th>Diskon</th>
                            <th>Min. Blj (Rp)</th>
                            <th>Periode</th>
                            <th>Sisa Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isiPromo">
                        {{-- Data awal dimuat oleh view tbody dari controller index --}}
                        @include('promos.tbody', ['promos' => $promos])
                    </tbody>
                </table>
            </div>
             {{-- Pagination Links (jika data awal pakai pagination) --}}
            <div class="mt-3">
                {{-- Pastikan $promos adalah object Paginator --}}
                @if ($promos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $promos->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    console.log("Document Ready! Script Manajemen Promo (AJAX) berjalan.");

    // === Variabel & Konstanta ===
    const isAdmin = @json(Auth::user()->level === 'admin');
    const formPromo = $('#formPromo');
    const formCardCollapseElement = document.getElementById('promoFormCardCollapse');
    const formCardCollapse = new bootstrap.Collapse(formCardCollapseElement, { toggle: false });
    const formTitlePromo = $('#formTitlePromo');
    const promoIdField = $('#promoId');
    const formModePromoField = $('#formModePromo');
    const errorMessagesPromo = $('#error-messages-promo');
    const btnSimpanPromo = $('#btnSimpanPromo'); // Tombol di dalam partial _form
    const btnCancelPromo = $('#btnCancelPromo'); // Tombol di dalam partial _form
    // Tombol konfirmasi delete tidak ada di form, langsung via Swal
    const btnToggleFormPromo = $('#btnToggleFormPromo');
    const btnToggleText = $('#btnToggleText');
    const filterDropdown = $('#filter_cabang');

    // --- Fungsi Helper ---

    // Fungsi format tanggal ke YYYY-MM-DD untuk input type="date"
    function formatDateToInput(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
             // Tambahkan 1 hari karena JS Date object sering bermasalah dengan timezone saat konversi
             // date.setDate(date.getDate() + 1); // Hati-hati jika tanggalnya sudah benar
             // return date.toISOString().split('T')[0]; // Format YYYY-MM-DD

             // Cara lain yg mungkin lebih stabil (jika format YYYY-MM-DD HH:MM:SS)
             return dateString.substring(0, 10); // Ambil YYYY-MM-DD

        } catch (e) {
            console.error("Error formatting date:", dateString, e);
            return '';
        }
    }

    function setFormStatePromo(mode = 'tambah', promoData = null) {
        console.log("Setting form state to:", mode, "with data:", promoData);
        formPromo[0].reset(); // Reset form
        errorMessagesPromo.hide().html(''); // Sembunyikan error
        formModePromoField.val(mode); // Set mode (tambah/edit)
        promoIdField.val(promoData ? promoData.id : ''); // Set ID jika edit
        formPromo.find('.is-invalid').removeClass('is-invalid'); // Hapus kelas error

        // Reset field spesifik ke default
        $('#cabang_id').val('');
        $('#khusus_member').prop('checked', false); // Default tidak checked
        $('#tipe_diskon').val('percentage'); // Default tipe persen
        $('input[name="status_promo"][value="1"]').prop('checked', true); // Default status aktif

        // Reset/Enable semua input di form
        formPromo.find('input, select, textarea').not('[type=hidden], #btnCancelPromo, #btnSimpanPromo').prop('disabled', false);

        if (mode === 'tambah') {
            formTitlePromo.text('Tambah Promo Baru');
            btnSimpanPromo.text('Simpan Promo Baru').show().prop('disabled', false);
            btnCancelPromo.hide(); // Tombol batal sembunyi saat tambah
            $('#cabang_id').prop('disabled', !isAdmin); // Admin bisa pilih cabang
            // (Form akan dibuka oleh event listener tombol toggle jika belum)

        } else if (mode === 'edit') {
            if (!promoData) { console.error("Edit mode called without promoData"); return; }
            formTitlePromo.text('Edit Promo: ' + promoData.nama_promo);
            populateFormPromo(promoData); // Isi form
            $('#cabang_id').prop('disabled', !isAdmin); // Admin bisa ubah cabang
            btnSimpanPromo.text('Update Promo').show().prop('disabled', false);
            btnCancelPromo.show(); // Tampilkan tombol batal
            formCardCollapse.show(); // Pastikan form terbuka
            btnToggleText.text('Tutup Form');

        }
         // Mode 'delete' tidak mengatur form lagi, langsung konfirmasi Swal

         // Scroll ke form jika mode edit (agar terlihat setelah klik tombol di tabel)
         if (mode === 'edit') {
             $('html, body').animate({ scrollTop: $(formCardCollapseElement).offset().top - 70 }, 300);
         }
    }

    function populateFormPromo(promo) {
        console.log("Populating form with:", promo);
        $('#nama_promo').val(promo.nama_promo);
        $('#deskripsi').val(promo.deskripsi);
        $('#cabang_id').val(promo.cabang_id || ''); // Set ke '' jika null
        $('#khusus_member').prop('checked', promo.khusus_member == 1 || promo.khusus_member === true);
        $('#tipe_diskon').val(promo.tipe_diskon);
        $('#nilai_diskon').val(promo.nilai_diskon);
        $('#minimal_total_harga').val(promo.minimal_total_harga);
        // Format tanggal untuk input type="date" (YYYY-MM-DD)
        $('#tanggal_mulai').val(formatDateToInput(promo.tanggal_mulai));
        $('#tanggal_selesai').val(formatDateToInput(promo.tanggal_selesai));
        // Set radio button status
        const statusValue = promo.status_promo ? '1' : '0';
        $(`input[name="status_promo"][value="${statusValue}"]`).prop('checked', true);
    }

    function displayErrorsPromo(errors) {
        let errorHtml = '<ul>';
        $.each(errors, function(key, value) { errorHtml += `<li>${value[0]}</li>`; });
        errorHtml += '</ul>';
        errorMessagesPromo.html(errorHtml).show();
    }

    // Fungsi refresh tabel (handle filter)
    function refreshPromoTable() {
        console.log("Refreshing Promo table...");
        let filterValue = 'all';
        // Hanya ambil filter jika elemennya ada dan user adalah admin
        if (isAdmin && filterDropdown.length > 0) {
            filterValue = filterDropdown.val() || 'all';
        }
        let dataUrl = "{{ route('promo.data') }}"; // Route untuk ambil HTML tbody
        // Tambahkan parameter filter jika bukan 'all'
        if (filterValue !== 'all') {
             // Pastikan nama parameter query cocok dengan yg dibaca controller getPromoData()
            dataUrl += "?filter_cabang=" + filterValue;
        }
        console.log("Refresh URL:", dataUrl);

        $('#isiPromo').html(`<tr><td colspan="10" class="text-center">Memuat data... <div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>`); // Colspan 10
        $.ajax({
            url: dataUrl, type: 'GET',
            success: function(data) {
                console.log("Promo data received for refresh.");
                $('#isiPromo').html(data); // Ganti isi tbody
                updateRemainingTimes(); // Update sisa waktu setelah tabel refresh
            },
            error: function(xhr) {
                console.error("Gagal memuat data tabel promo:", xhr);
                $('#isiPromo').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        });
    }

    // Fungsi Kalkulasi Sisa Waktu
    function updateRemainingTimes() {
        console.log('Updating remaining times...');
        const now = new Date();
        $('.sisa-waktu').each(function() {
            const endDateString = $(this).data('end-date'); // Ambil ISO string
            const targetSpan = $(this);
            targetSpan.html('<span class="placeholder col-8 placeholder-sm"></span>');

            if (!endDateString) {
                 targetSpan.html('<span class="text-muted">-</span>'); return;
            }

            try {
                const endDate = new Date(endDateString);
                const diff = endDate.getTime() - now.getTime();

                if (diff <= 0) {
                    targetSpan.html('<span class="badge bg-secondary">Kadaluarsa</span>');
                } else {
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    let remainingText = '';
                    if (days > 0) { remainingText += `${days} hr `; }
                    if (hours > 0 || days > 0) { remainingText += `${hours} jm `; }
                    remainingText += `${minutes} mnt`;
                    let badgeClass = 'bg-success';
                    if (days < 1) { badgeClass = 'bg-warning text-dark'; }
                    else if (days < 3) { badgeClass = 'bg-info'; }
                    targetSpan.html(`<span class="badge ${badgeClass}">${remainingText}</span>`);
                }
            } catch (e) {
                console.error("Error parsing/calculating date diff for:", endDateString, e);
                targetSpan.html('<span class="text-danger">Error Tgl</span>');
            }
        });
    }

    // --- Event Listeners ---

    // Tombol Buka/Tutup Form
    btnToggleFormPromo.click(function() {
        if (formCardCollapseElement.classList.contains('show')) {
            formCardCollapse.hide();
        } else {
             // Jika form akan dibuka, pastikan dalam mode tambah (kecuali sedang edit)
            if (formModePromoField.val() !== 'edit') {
                 setFormStatePromo('tambah');
            }
            formCardCollapse.show();
        }
    });
     // Update teks tombol saat collapse state berubah
     formCardCollapseElement.addEventListener('shown.bs.collapse', () => {
         btnToggleText.text('Tutup Form');
     });
     formCardCollapseElement.addEventListener('hidden.bs.collapse', () => {
         btnToggleText.text('Tambah Promo');
          // Reset ke mode tambah jika form ditutup BUKAN dari tombol batal
          if(formModePromoField.val() !== 'tambah' && !$(this).is('#btnCancelPromo')) {
                // Cek apakah penutupan BUKAN karena tombol Batal di klik
                // Mungkin perlu flag tambahan jika ingin lebih presisi
                setFormStatePromo('tambah');
          }
     });


    // Tombol Edit di Tabel
    $(document).on('click', '.btn-edit-promo', function() {
        const promoJsonString = $(this).data('promo');
        try {
            let promo = (typeof promoJsonString === 'object') ? promoJsonString : JSON.parse(promoJsonString);
            console.log("Edit Promo clicked. Data:", promo);
            if (promo && promo.id) {
                promoIdField.val(promo.id);
                setFormStatePromo('edit', promo);
            } else { throw new Error('Invalid promo data for edit.'); }
        } catch (e) {
            console.error("Error parsing promo data for edit:", e, promoJsonString);
            Swal.fire('Error', 'Gagal membaca data promo.', 'error');
        }
    });

    // Tombol Hapus di Tabel
    $(document).on('click', '.btn-hapus-promo', function() {
        const promoId = $(this).data('id');
        const promoName = $(this).data('name');

        if (!promoId || !promoName) {
            console.error("Missing data-id or data-name on delete button.");
            Swal.fire('Error', 'Data promo tidak lengkap untuk dihapus.', 'error');
            return;
        }
        console.log("Delete button clicked for ID:", promoId, "Name:", promoName);

        Swal.fire({
            title: 'Anda Yakin?', html: `Anda akan menghapus promo: <strong>${promoName}</strong>?`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                let urlTemplate = "{{ route('promo.destroy', ['promo' => ':id']) }}"; // Parameter {promo}
                let url = urlTemplate.replace(':id', promoId);
                console.log("Sending DELETE request to:", url);
                $.ajax({
                    url: url, type: 'POST', data: { _method: 'DELETE', _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if (response.success) {
                            refreshPromoTable();
                            // Jika form sedang terbuka dan menampilkan data yg dihapus, reset form
                            if (formModePromoField.val() === 'edit' && promoIdField.val() == promoId) {
                                 setFormStatePromo('tambah');
                                 formCardCollapse.hide();
                                 btnToggleText.text('Tambah Promo');
                            }
                            Swal.fire('Dihapus!', response.message, 'success');
                        } else { Swal.fire('Gagal!', response.message || 'Error.', 'error'); }
                    },
                    error: function (xhr) { console.error("AJAX Delete Error:", xhr); Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Error.'), 'error'); }
                });
            }
        });
    });

    // Tombol Batal di Form
    // Pastikan tombol ini ada di _form.blade.php dengan ID btnCancelPromo
    $('#btnCancelPromo').click(function() {
        console.log("Cancel clicked");
        setFormStatePromo('tambah'); // Kembali ke mode tambah
        formCardCollapse.hide(); // Tutup form juga
        btnToggleText.text('Tambah Promo');
    });

    // Form Submit (Tambah/Edit)
    formPromo.submit(function (e) {
        e.preventDefault();
        btnSimpanPromo.prop('disabled', true).text('Menyimpan...');
        errorMessagesPromo.hide().html('');

        let mode = formModePromoField.val();
        let promoId = promoIdField.val();
        let url = '';
        let formData = new FormData(this);
        let method = 'POST';

        // Handle checkbox 'khusus_member' (jika tidak tercentang, FormData tidak menyertakannya)
        if (!formData.has('khusus_member')) {
            formData.append('khusus_member', '0');
        } else {
             formData.set('khusus_member', '1'); // Pastikan value 1 jika checked
        }
        // Handle radio 'status_promo' (nilai 0 atau 1 sudah otomatis terkirim)

        if (mode === 'tambah') {
            url = "{{ route('promo.store') }}";
        } else if (mode === 'edit') {
            urlTemplate = "{{ route('promo.update', ['promo' => ':id']) }}"; // Parameter {promo}
            url = urlTemplate.replace(':id', promoId);
            formData.append('_method', 'PUT');
            // Jika cabang disabled (bukan admin), hapus dari data agar tidak terupdate
             if (isAdmin && $('#cabang_id').is(':disabled')) { // Jika admin tapi field disabled (seharusnya tidak terjadi)
                 formData.delete('cabang_id');
             } else if (!isAdmin) { // Jika bukan admin, jangan kirim cabang_id
                 formData.delete('cabang_id');
             }
        } else { return; }

        console.log("Submitting promo data. Mode:", mode, "URL:", url);
        // for (let [key, value] of formData.entries()) { console.log(key, value); } // Debug FormData

        $.ajax({
            url: url, type: method, data: formData, processData: false, contentType: false,
            success: function (response) {
                if (response.success) {
                    refreshPromoTable();
                    setFormStatePromo('tambah');
                    formCardCollapse.hide();
                    btnToggleText.text('Tambah Promo');
                    Swal.fire('Berhasil!', response.message, 'success');
                } else {
                    Swal.fire('Gagal!', response.message || 'Error server.', 'error');
                    btnSimpanPromo.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Promo Baru' : 'Update Promo');
                }
            },
            error: function (xhr) {
                btnSimpanPromo.prop('disabled', false).text(mode === 'tambah' ? 'Simpan Promo Baru' : 'Update Promo');
                if (xhr.status === 422) {
                    displayErrorsPromo(xhr.responseJSON.errors);
                    Swal.fire('Error Validasi', 'Periksa kembali isian form.', 'error');
                } else {
                    console.error("AJAX Error:", xhr);
                    Swal.fire('Error ' + xhr.status, (xhr.responseJSON?.message || 'Error server.'), 'error');
                }
            }
        });
    });

    // Filter Change (Hanya Admin) - Menggunakan AJAX untuk refresh tabel
    if (isAdmin) {
        filterDropdown.change(function() {
             refreshPromoTable(); // Panggil refresh saat filter berubah
        });
    }

    // --- Inisialisasi ---
    updateRemainingTimes(); // Hitung sisa waktu saat halaman dimuat
    // setInterval(updateRemainingTimes, 60000); // Update setiap menit (opsional)

});
</script>
@endpush
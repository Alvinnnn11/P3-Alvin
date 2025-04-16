<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Cabang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(120deg, #f6f9fc, #e9eff5);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .selection-container {
            max-width: 700px;
            margin-top: 5rem;
        }
        .branch-card {
            background-color: #fff;
            border: none;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .branch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0,0,0,0.1);
        }
        .branch-logo {
            max-height: 60px;
            max-width: 100px;
            object-fit: contain;
        }
        .card-header {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .logout-link {
            font-size: 0.9rem;
            color: #6c757d;
            text-decoration: none;
        }
        .logout-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="container selection-container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center py-3">
            <h4 class="mb-0">Selamat Datang, {{ Auth::user()->name }}!</h4>
        </div>
        <div class="card-body p-4">
            <p class="text-center mb-4">Silakan pilih cabang yang ingin Anda akses:</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if ($availableCabangs->isEmpty())
                <div class="alert alert-warning text-center">
                    Saat ini belum ada cabang yang tersedia. Silakan hubungi administrator.
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="btn btn-secondary btn-sm">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="GET" style="display: none;"></form>
                </div>
            @else
                <form action="{{ route('cabang.storeSelection') }}" method="POST" id="selectCabangForm">
                    @csrf
                    <input type="hidden" name="cabang_id" id="selected_cabang_id">
                    <div class="row">
                        @foreach ($availableCabangs as $cabang)
                            <div class="col-md-6">
                                <button type="button" class="branch-card w-100 text-start branch-select-btn" data-id="{{ $cabang->id }}">
                                    <div class="d-flex align-items-center">
                                        @if($cabang->logo_perusahaan)
                                            <img src="{{ asset('storage/' . $cabang->logo_perusahaan) }}" alt="Logo" class="branch-logo me-3">
                                        @else
                                            <i class="fas fa-building fa-2x text-muted me-3"></i>
                                        @endif
                                        <div>
                                            <h5 class="mb-1 text-primary">{{ $cabang->nama_perusahaan }}</h5>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ Str::limit($cabang->alamat_perusahaan, 50) }}
                                            </small>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form-bottom').submit();"
           class="logout-link">
            <i class="fas fa-sign-out-alt me-1"></i> Logout dari akun
        </a>
        <form id="logout-form-bottom" action="{{ route('logout') }}" method="GET" style="display: none;"></form>
    </div>
</div>

<script>
    document.querySelectorAll('.branch-select-btn').forEach(button => {
        button.addEventListener('click', function () {
            const selectedId = this.getAttribute('data-id');
            document.getElementById('selected_cabang_id').value = selectedId;
            document.getElementById('selectCabangForm').submit();
        });
    });
</script>
</body>
</html>

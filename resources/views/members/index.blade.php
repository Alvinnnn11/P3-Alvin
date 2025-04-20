@extends('layout.template')
@section('title', 'Member')

@section('content')
@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.bootstrap5.css">
@endpush

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Data Member</h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item">Setting</li>
                    <li class="breadcrumb-item active" aria-current="page">Member</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Member</h3></div>
                    <div class="col-12 d-flex">
                        <a href="{{ url('setting/member/invoice') }}" class="btn btn-warning ms-3 mt-3">
                            Invoice
                        </a>
                    

                    </div>
                    <div class="card-body">
                        <table id="dataTablemember" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Member</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Status Member</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($member as $m)
                                <tr>
                                    <td>{{ $loop->iteration  }}</td>
                                    <td>{{ $m->user->name  }}</td>
                                    <td>{{ $m->joined_at  }}</td>
                                    <td>{{ $m->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>
</div>


@push('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.bootstrap5.js"></script>
    <script>
        new DataTable('#dataTableCategory');
    </script>
@endpush
@endsection
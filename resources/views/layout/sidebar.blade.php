<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <!-- Brand & Logo -->
    <div class="app-brand demo d-flex align-items-center p-3 border-bottom">
      <a href="index-2.html" class="app-brand-link d-flex align-items-center w-100">
        @php
        $cabangInfo = session('assigned_cabang'); // Ambil data Cabang dari session
        $globalSetting = null; // Inisialisasi
        if (!$cabangInfo) {
            // Jika tidak ada info cabang di session, ambil setting global
            $globalSetting = \App\Models\Setting::first();
        }
    @endphp
     <div class="logo-container me-3">
      @if($cabangInfo && $cabangInfo->logo_perusahaan)
          {{-- Tampilkan Logo Cabang jika ada --}}
          <img src="{{ asset('storage/' . $cabangInfo->logo_perusahaan) }}" alt="Logo Cabang" class="img-fluid">
      @elseif($globalSetting && $globalSetting->logo)
           {{-- Tampilkan Logo Global jika tidak ada logo cabang --}}
          <img src="{{ asset('storage/back/logo/' . $globalSetting->logo) }}" alt="Logo Perusahaan" class="img-fluid">
      @else
          {{-- Fallback jika tidak ada logo sama sekali --}}
          <span class="fw-bold text-primary" style="font-size: 24px;">?</span> {{-- Atau logo default lain --}}
      @endif
  </div>
  
  <div class="company-name flex-grow-1">
    <h3 class="fw-bold mb-0 text-white">
        @if($cabangInfo)
            {{-- Tampilkan Nama Cabang --}}
            {{ $cabangInfo->nama_perusahaan }}
        @elseif($globalSetting)
             {{-- Tampilkan Nama Global --}}
            {{ $globalSetting->nama_perusahaan }}
        @else
            {{-- Fallback --}}
            Nama Perusahaan
        @endif
    </h3>
    {{-- Tambahkan alamat cabang di bawah nama (opsional) --}}
    @if($cabangInfo)
        <small class="text-white d-block" style="font-size: 0.75rem; line-height: 1;">{{ Str::limit($cabangInfo->alamat_perusahaan, 30) }}</small>
    @endif
</div>
 {{-- AKHIR LOGIKA BARU --}}
</a>
      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto text-white">
        <i class="bx bx-chevron-left"></i>
      </a>
    </div>
  
    <div class="menu-inner-shadow"></div>
  
    @php
    $userLevel = auth()->user()->level ?? 'user';
    @endphp
  
    <ul class="menu-inner py-1">
        <!-- Dashboard (Semua Level Bisa Akses) -->
        <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <a href="/dashboard" class="menu-link">
                <i class="menu-icon bx bx-home"></i>
                <div>Dashboard</div>
            </a>
        </li>
      
  
        <!-- Master Data (Admin & Supervisor) -->
        {{-- @if ($userLevel === 'admin' || $userLevel === 'supervisor' || $userLevel === 'petugas' || $userLevel === 'teknisi') --}}
        <li class="menu-header small">Master Data</li>
        <li class="menu-item has-submenu {{ request()->is('merk', 'type', 'kategori', 'satuan', 'sparepart', 'alat') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-layer"></i>
                <div>Data Master</div>
                <i class="bx bx-chevron-down ms-auto"></i>
            </a>
            <ul class="menu-sub">
                {{-- @if ($userLevel === 'admin' || $userLevel === 'supervisor') --}}
                <li class="menu-item {{ request()->is('cabang') ? 'active' : '' }}"><a href="/cabang" class="menu-link"><i class="bx bx-home"></i> Cabang</a></li>
                <li class="menu-item {{ request()->is('type') ? 'active' : '' }}"><a href="/type" class="menu-link"><i class="bx bx-shape-circle"></i> Promo</a></li>
                <li class="menu-item {{ request()->is('kategori') ? 'active' : '' }}"><a href="/kategori" class="menu-link"><i class="bx bx-category"></i> Layanan</a></li>
                <li class="menu-item {{ request()->is('satuan') ? 'active' : '' }}"><a href="/satuan" class="menu-link"><i class="bx bx-box"></i> Satuan</a></li>
                {{-- @endif --}}
                {{-- @if ($userLevel === 'admin' || $userLevel === 'supervisor' || $userLevel === 'petugas' || $userLevel === 'teknisi') --}}
                {{-- <li class="menu-item {{ request()->is('alat') ? 'active' : '' }}"><a href="/alat" class="menu-link"><i class="bx bx-tool"></i> Alat</a></li>
                <li class="menu-item {{ request()->is('sparepart') ? 'active' : '' }}"><a href="/sparepart" class="menu-link"><i class="bx bx-wrench"></i> Data Sparepart</a></li> --}}
                {{-- @endif --}}
            </ul>
        </li>
        {{-- @endif --}}
  
        <!-- Transaksi (Admin, Supervisor, Petugas, Teknisi) -->
        {{-- @if ($userLevel !== 'user') --}}
        <li class="menu-header small">Transaksi</li>
        <li class="menu-item has-submenu {{ request()->is('peminjaman', 'services', 'stok-transaksi') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-money"></i>
                <div>Transaksi</div>
                <i class="bx bx-chevron-down ms-auto"></i>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('peminjaman') ? 'active' : '' }}"><a href="/peminjaman" class="menu-link"><i class="bx bx-calendar-check"></i> Pesanan Laundry</a></li>
                <li class="menu-item {{ request()->is('services') ? 'active' : '' }}"><a href="/services" class="menu-link"><i class="bx bx-package"></i> Item Pesanan</a></li>
                <li class="menu-item {{ request()->is('task') ? 'active' : '' }}"><a href="/tasks" class="menu-link"><i class="bx bx-package"></i> Pembayaran</a></li>
                {{-- @if ($userLevel === 'admin' || $userLevel === 'supervisor') --}}
                <li class="menu-item {{ request()->is('stok-transaksi') ? 'active' : '' }}"><a href="/stok-transaksi" class="menu-link"><i class="bx bx-package"></i> Riwayat pemesanan</a></li>
                {{-- @endif --}}
            </ul>
        </li>
        {{-- @endif --}}
  
        <!-- Manajemen Kehadiran (Admin, Supervisor, Petugas, Teknisi) -->
        {{-- @if ($userLevel !== 'user') --}}
      
        {{-- @endif --}}
  
        <!-- Manajemen Pengguna -->
        {{-- @if ($userLevel !== 'user') --}}
        <li class="menu-header small">Pengguna</li>
        <li class="menu-item {{ request()->is('users') ? 'active' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-user"></i>
                <div>Manajemen Pengguna</div>
            </a>
            <ul class="menu-sub">
              <li class="menu-item {{ request()->is('users') ? 'active' : '' }}"><a href="/users" class="menu-link"><i class="bx bx-user"></i>  Data User</a></li>
              <li class="menu-item {{ request()->is('services') ? 'active' : '' }}"><a href="/sepervisors" class="menu-link"><i class="bx bx-user"></i> Data Supervisor</a></li>
              <li class="menu-item {{ request()->is('petugas') ? 'active' : '' }}"><a href="/petugas" class="menu-link"><i class="bx bx-user"></i> Data Petugas</a></li>
              <li class="menu-item {{ request()->is('services') ? 'active' : '' }}"><a href="/admins" class="menu-link"><i class="bx bx-user"></i> Data admin</a></li>
              <li class="menu-item {{ request()->is('task') ? 'active' : '' }}"><a href="/tasks" class="menu-link"><i class="bx bx-user"></i> Member</a></li>
              <li class="menu-item {{ request()->is('task') ? 'active' : '' }}"><a href="/pengguna" class="menu-link"><i class="bx bx-user"></i> pengguna</a></li>
              {{-- @if ($userLevel === 'admin' || $userLevel === 'supervisor') --}}
             
              {{-- @endif --}}
          </ul>
        </li>
        {{-- @endif --}}
  
        <!-- Pengaturan (Hanya Admin) -->
        {{-- @if ($userLevel === 'admin') --}}
        <li class="menu-header small">Pengaturan</li>
        <li class="menu-item {{ request()->is('setting*') ? 'active' : '' }}">
            <a href="{{ route('setting.index') }}" class="menu-link">
                <i class="menu-icon bx bx-wrench"></i>
                <div>Pengaturan Sistem</div>
            </a>
        </li>
        {{-- @endif --}}
    </ul>
  </aside>  
  
  <!-- Mobile Menu Toggle -->
  <div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-white p-2 rounded-1 bg-dark">
      <i class="bx bx-menu"></i>
    </a>
  </div>
  
  <!-- CSS untuk sidebar -->
  <style>
  .layout-menu {
    width: 250px;
    background-color: #007bff;
    color: white;
    transition: all 0.3s ease-in-out;
  }
  
  .logo-container {
    width: 60px;
    height: 60px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: white;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
  }
  
  .menu-inner {
    padding: 0;
  }
  
  .menu-item {
    list-style: none;
  }
  
  .menu-item a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: white;
    text-decoration: none;
    transition: background 0.3s ease-in-out;
  }
  
  .menu-item a:hover {
    background-color: #0056b3;
  }
  
  .menu-item.active > a {
    background-color: white;
    color: #007bff;
    font-weight: bold;
    border-radius: 5px;
  }
  
  .menu-sub {
    background-color: #0056b3;
    padding-left: 15px;
    display: none;
  }
  
  .menu-item.open .menu-sub {
    display: block;
    transition: all 0.3s ease-in-out;
  }
  
  .menu-icon {
    margin-right: 10px;
    font-size: 18px;
  }
  
  .menu-item.open .menu-toggle i {
    transform: rotate(90deg);
    transition: transform 0.3s ease-in-out;
  }
  </style>
  
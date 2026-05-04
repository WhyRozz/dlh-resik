<aside class="sidebar" id="sidebar">
    {{-- Header Sidebar --}}
    <div class="sidebar-header">
        <img src="{{ asset('assets/logo-resik.png') }}" alt="RESIK Logo" class="logo">
        <button class="sidebar-close" aria-label="Tutup Menu" onclick="toggleSidebar()">&times;</button>
    </div>

    {{-- Navigasi Menu --}}
    <nav class="sidebar-nav">
        <ul class="nav-list">
            {{-- Beranda --}}
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <img src="{{ asset('assets/icons/beranda.png') }}" alt="Beranda" class="custom-icon">
                    <span>Beranda</span>
                </a>
            </li>

            {{-- Laporan Sampah Ilegal --}}
            <li class="nav-item {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
                <a href="{{ route('admin.laporan.index') }}">
                    <img src="{{ asset('assets/icons/laporan_sampah.png') }}" alt="Laporan" class="custom-icon">
                    <span>Laporan Sampah Ilegal</span>
                </a>
            </li>

            {{-- Dropdown: Bank Sampah --}}
            <li class="nav-item has-dropdown {{ request()->routeIs('admin.bank-sampah*') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="dropdown-toggle" aria-expanded="false" onclick="toggleDropdown(this)">
                    <div class="nav-link-text">
                        <img src="{{ asset('assets/icons/bank_sampah.png') }}" alt="Bank-Sampah" class="custom-icon">
                        <span>Bank Sampah</span>
                    </div>
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('admin.bank-sampah.setor') }}" class="{{ request()->routeIs('admin.bank-sampah.setor') ? 'active' : '' }}">Data Setor</a></li>
                    <li><a href="{{ route('admin.bank-sampah.penarikan.index') }}" class="{{ request()->routeIs('admin.bank-sampah.penarikan.index') ? 'active' : '' }}">Data Penarikan</a></li>
                    <li><a href="{{ route('admin.bank-sampah.jenis-sampah.index') }}" class="{{ request()->routeIs('admin.bank-sampah.jenis-sampah.index') ? 'active' : '' }}">Jenis & Harga Sampah</a></li>
                    <li>
                        <a href="{{ route('admin.bank-sampah.penjemputan.index') }}" 
                        class="{{ request()->routeIs('admin.bank-sampah.penjemputan*') ? 'active' : '' }}">
                            Penjemputan
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Artikel Edukasi --}}
            <li class="nav-item {{ request()->routeIs('admin.artikel*') ? 'active' : '' }}">
                <a href="{{ route('admin.artikel.index') }}">
                    <img src="{{ asset('assets/icons/artikel.png') }}" alt="Artikel" class="custom-icon">
                    <span>Artikel Edukasi</span>
                </a>
            </li>

            {{-- Informasi TPS --}}
            <li class="nav-item {{ request()->routeIs('admin.tps*') ? 'active' : '' }}">
                <a href="{{ route('admin.tps.index') }}">
                    <img src="{{ asset('assets/icons/tps2.png') }}" alt="TPS" class="custom-icon">
                    <span>Informasi TPS</span>
                </a>
            </li>

            {{-- Data Pengguna --}}
            <li class="nav-item {{ request()->routeIs('admin.data-pengguna*') ? 'active' : '' }}">
                <a href="{{ route('admin.data-pengguna.index') }}">
                    <img src="{{ asset('assets/icons/data_pengguna.png') }}" alt="Data-Pengguna" class="custom-icon">
                    <span>Data Pengguna</span>
                </a>
            </li>

            {{-- Kelola Akun --}}
            <li class="nav-item {{ request()->routeIs('admin.akun*') ? 'active' : '' }}">
                <a href="{{ route('admin.akun.index') }}">
                    <img src="{{ asset('assets/icons/kelola_akun.png') }}" alt="Kelola-Akun" class="custom-icon">
                    <span>Kelola Akun</span>
                </a>
            </li>
        </ul>

        {{-- Footer / Logout --}}
        <div class="sidebar-footer">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <img src="{{ asset('assets/icons/keluar.png') }}" alt="Logout" class="custom-icon">
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </nav>
</aside>

{{-- Overlay untuk Mobile --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

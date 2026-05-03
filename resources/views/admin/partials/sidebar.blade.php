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
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
            </li>

            {{-- Laporan Sampah Ilegal --}}
            <li class="nav-item {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
                <a href="{{ route('admin.laporan.index') }}">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Laporan Sampah Ilegal</span>
                </a>
            </li>

            {{-- Dropdown: Bank Sampah --}}
            <li class="nav-item has-dropdown {{ request()->routeIs('admin.bank-sampah*') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="dropdown-toggle" aria-expanded="false" onclick="toggleDropdown(this)">
                    <div class="nav-link-text">
                        <i class="fas fa-recycle"></i>
                        <span>Bank Sampah</span>
                    </div>
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('admin.bank-sampah.setor') }}" class="{{ request()->routeIs('admin.bank-sampah.setor') ? 'active' : '' }}">Data Setor</a></li>
                    <li><a href="{{ route('admin.bank-sampah.tarik') }}" class="{{ request()->routeIs('admin.bank-sampah.tarik') ? 'active' : '' }}">Data Penarikan</a></li>
                    <li><a href="{{ route('admin.bank-sampah.jenis-sampah.index') }}" class="{{ request()->routeIs('admin.bank-sampah.jenis-sampah') ? 'active' : '' }}">Jenis & Harga Sampah</a></li>
                    <li><a href="{{ route('admin.bank-sampah.penjemputan.index') }}" class="{{ request()->routeIs('admin.bank-sampah.penjemputan') ? 'active' : '' }}">Penjemputan</a></li>
                </ul>
            </li>

            {{-- Artikel Edukasi --}}
            <li class="nav-item {{ request()->routeIs('admin.artikel*') ? 'active' : '' }}">
                <a href="{{ route('admin.artikel.index') }}">
                    <i class="fas fa-newspaper"></i>
                    <span>Artikel Edukasi</span>
                </a>
            </li>

            {{-- Informasi TPS --}}
            <li class="nav-item {{ request()->routeIs('admin.tps*') ? 'active' : '' }}">
                <a href="{{ route('admin.tps.index') }}">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Informasi TPS</span>
                </a>
            </li>

            {{-- Data Pengguna --}}
            <li class="nav-item {{ request()->routeIs('admin.data-pengguna*') ? 'active' : '' }}">
                <a href="{{ route('admin.data-pengguna.index') }}">
                    <i class="fas fa-users"></i>
                    <span>Data Pengguna</span>
                </a>
            </li>

            {{-- Kelola Akun --}}
            <li class="nav-item {{ request()->routeIs('admin.akun*') ? 'active' : '' }}">
                <a href="{{ route('admin.akun.index') }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Kelola Akun</span>
                </a>
            </li>
        </ul>

        {{-- Footer / Logout --}}
        <div class="sidebar-footer">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>
</aside>

{{-- Overlay untuk Mobile --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

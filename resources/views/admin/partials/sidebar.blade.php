<aside class="sidebar" id="sidebar">
    <!-- Fungsi: Header sidebar berisi logo dan tombol close untuk mobile -->
    <div class="sidebar-header">
        <!-- Fungsi: Menampilkan logo aplikasi RESIK -->
        <img src="{{ asset('assets/logo-resik.png') }}" alt="RESIK Logo" class="logo">
        <!-- Fungsi: Tombol close sidebar untuk tampilan mobile dengan accessibility label -->
        <button class="sidebar-close" aria-label="Tutup Menu" onclick="toggleSidebar()">&times;</button>
    </div>

    <!-- Fungsi: Container navigasi menu sidebar -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <!-- Fungsi: Menu Beranda dengan active state berdasarkan route saat ini -->
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <!-- Fungsi: Icon menu Beranda -->
                    <img src="{{ asset('assets/icons/beranda.png') }}" alt="Beranda" class="custom-icon">
                    <span>Beranda</span>
                </a>
            </li>

            <!-- Fungsi: Menu Laporan Sampah Ilegal dengan wildcard route matching -->
            <li class="nav-item {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
                <a href="{{ route('admin.laporan.index') }}">
                    <img src="{{ asset('assets/icons/laporan_sampah.png') }}" alt="Laporan" class="custom-icon">
                    <span>Laporan Sampah Ilegal</span>
                </a>
                {{-- ✅ Badge DIPINDAH ke DALAM <li> --}}
                <span class="notif-badge-wrapper" onclick="openNotifModal('laporan')" title="Lihat notifikasi">
                    <span id="badge-laporan" class="notif-badge" style="display:none;">0</span>
                </span>
            </li>

            <!-- Fungsi: Menu dropdown Bank Sampah dengan toggle expand/collapse -->
            <li class="nav-item has-dropdown {{ request()->routeIs('admin.bank-sampah*') ? 'active open' : '' }}">
                <!-- Fungsi: Trigger dropdown dengan onclick toggle -->
                <a href="javascript:void(0)" class="dropdown-toggle" aria-expanded="false"
                    onclick="toggleDropdown(this)">
                    <div class="nav-link-text">
                        <!-- Fungsi: Icon menu Bank Sampah -->
                        <img src="{{ asset('assets/icons/bank_sampah.png') }}" alt="Bank-Sampah" class="custom-icon">
                        <span>Bank Sampah</span>
                    </div>
                    <!-- Fungsi: Icon panah dropdown yang berputar saat aktif -->
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
                <!-- Fungsi: Sub-menu untuk Bank Sampah -->
                <ul class="sub-menu">
                    <!-- Fungsi: Sub-menu Data Setor dengan active state -->
                    <li>
                        <a href="{{ route('admin.bank-sampah.setor.index') }}"
                            class="{{ request()->routeIs('admin.bank-sampah.setor.index') ? 'active' : '' }}">
                            Data Setor
                        </a>
                    </li>
                    <!-- Fungsi: Sub-menu Data Penarikan -->
                    <li><a href="{{ route('admin.bank-sampah.penarikan.index') }}"
                            class="{{ request()->routeIs('admin.bank-sampah.penarikan.index') ? 'active' : '' }}">Data
                            Penarikan</a>
                        <!-- ✅ Badge Notifikasi -->
                        <span class="notif-badge-wrapper" onclick="openNotifModal('penarikan')"
                            title="Lihat notifikasi">
                            <span id="badge-penarikan" class="notif-badge" style="display:none;">0</span>
                        </span>
                    </li>
                    <!-- Fungsi: Sub-menu Jenis & Harga Sampah -->
                    <li><a href="{{ route('admin.bank-sampah.jenis-sampah.index') }}"
                            class="{{ request()->routeIs('admin.bank-sampah.jenis-sampah.index') ? 'active' : '' }}">Jenis
                            & Harga Sampah</a></li>


                    <!-- Fungsi: Sub-menu Penjemputan -->
                    <li>
                        <a href="{{ route('admin.bank-sampah.penjemputan.index') }}"
                            class="{{ request()->routeIs('admin.bank-sampah.penjemputan*') ? 'active' : '' }}">
                            Penjemputan
                        </a>

                        <!-- Badge Notifikasi (Struktur sama dengan Data Penarikan) -->
                        <span class="notif-badge-wrapper"
                            onclick="openNotifModal('penjemputan'); event.stopPropagation(); return false;"
                            title="Lihat notifikasi">
                            <span id="badge-penjemputan" class="notif-badge" style="display:none;">0</span>
                        </span>
                    </li>
                </ul>
            </li>

            <!-- Fungsi: Menu Artikel Edukasi dengan wildcard route matching -->
            <li class="nav-item {{ request()->routeIs('admin.artikel*') ? 'active' : '' }}">
                <a href="{{ route('admin.artikel.index') }}">
                    <!-- Fungsi: Icon menu Artikel -->
                    <img src="{{ asset('assets/icons/artikel.png') }}" alt="Artikel" class="custom-icon">
                    <span>Artikel Edukasi</span>
                </a>
            </li>

            <!-- Fungsi: Menu Informasi TPS dengan wildcard route matching -->
            <li class="nav-item {{ request()->routeIs('admin.tps*') ? 'active' : '' }}">
                <a href="{{ route('admin.tps.index') }}">
                    <!-- Fungsi: Icon menu TPS -->
                    <img src="{{ asset('assets/icons/tps2.png') }}" alt="TPS" class="custom-icon">
                    <span>Informasi TPS</span>
                </a>
            </li>

            <!-- Fungsi: Menu Data Pengguna dengan wildcard route matching -->
            <li class="nav-item {{ request()->routeIs('admin.data-pengguna*') ? 'active' : '' }}">
                <a href="{{ route('admin.data-pengguna.index') }}">
                    <!-- Fungsi: Icon menu Data Pengguna -->
                    <img src="{{ asset('assets/icons/data_pengguna.png') }}" alt="Data-Pengguna" class="custom-icon">
                    <span>Data Pengguna</span>
                </a>
            </li>

            {{-- Fungsi: Menu dropdown Kelola Akun dengan toggle expand/collapse --}}
            <li
                class="nav-item has-dropdown {{ request()->routeIs('admin.akun*') || request()->routeIs('admin.sub-admin*') ? 'active open' : '' }}">
                {{-- Fungsi: Trigger dropdown dengan onclick toggle --}}
                <a href="javascript:void(0)" class="dropdown-toggle" aria-expanded="false"
                    onclick="toggleDropdown(this)">
                    <div class="nav-link-text">
                        {{-- Fungsi: Icon menu Kelola Akun --}}
                        <img src="{{ asset('assets/icons/kelola_akun.png') }}" alt="Kelola-Akun" class="custom-icon">
                        <span>Kelola Akun</span>
                    </div>
                    {{-- Fungsi: Icon panah dropdown yang berputar saat aktif --}}
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
                {{-- Fungsi: Sub-menu untuk Kelola Akun --}}
                <ul class="sub-menu">
                    {{-- Fungsi: Sub-menu Super Admin & Petugas --}}
                    <li>
                        <a href="{{ route('admin.akun.index') }}"
                            class="{{ request()->routeIs('admin.akun.index') ? 'active' : '' }}">
                            Super Admin & Petugas
                        </a>
                    </li>
                    {{-- Fungsi: Sub-menu Sub Admin Desa (Coming Soon) --}}
                    <li>
                        <a href="javascript:void(0)" class="coming-soon" title="Fitur ini sedang dalam pengembangan">
                            Sub Admin Desa
                            <span class="badge badge-info"
                                style="font-size: 10px; padding: 2px 6px; margin-left: 5px;">Soon</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>

        <!-- Fungsi: Footer sidebar berisi tombol logout -->
        <div class="sidebar-footer">
            <!-- Fungsi: Form logout dengan method POST dan CSRF token untuk keamanan -->
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <!-- Fungsi: Tombol submit logout dengan icon dan label -->
                <button type="submit" class="logout-btn">
                    <!-- Fungsi: Icon tombol Keluar -->
                    <img src="{{ asset('assets/icons/keluar.png') }}" alt="Logout" class="custom-icon">
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </nav>
</aside>

{{-- 
    Mobile Hamburger Navbar - Complete Component
    Usage: @include('partials.mobile-navbar')
--}}

@php
    // Get current user info
    $user = auth()->user();
    $userInitial = $user ? strtoupper(substr($user->name ?? 'A', 0, 1)) : 'A';

    // Get notification counts
    $laporanCount = \App\Models\Laporan::where('status', 'Pending')->count() ?? 0;
    $penarikanCount = \App\Models\Penarikan::where('status', 'diproses')->count() ?? 0;
@endphp

<nav class="mobile-navbar">
    <div class="mobile-navbar-brand">
        {{-- <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="mobile-logo"> --}}
        {{-- <span class="mobile-title">@yield('page-title-mobile', 'RESIK')</span> --}}
    </div>

    {{-- Hamburger Button --}}
    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Dropdown Menu --}}
    <div class="mobile-menu-dropdown" id="mobileMenuDropdown">
        {{-- User Info Header --}}
        <div class="mobile-menu-header">
            <div class="mobile-menu-user">
                <div class="mobile-menu-avatar">{{ $userInitial }}</div>
                <div class="mobile-menu-user-info">
                    <h4>{{ $user->name ?? 'Admin' }}</h4>
                    <p>{{ $user->email ?? 'admin@resik.com' }}</p>
                </div>
            </div>
        </div>

        {{-- Menu Items --}}
        <ul class="mobile-menu-list">
            {{-- Beranda --}}
            <li class="mobile-menu-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="mobile-menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
            </li>

            {{-- Laporan Sampah Ilegal --}}
            <li class="mobile-menu-item">
                <a href="{{ route('admin.laporan.index') }}"
                    class="mobile-menu-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Laporan Sampah Ilegal</span>
                    @if ($laporanCount > 0)
                        <span class="mobile-menu-badge">{{ $laporanCount }}</span>
                    @endif
                </a>
            </li>

            {{-- Bank Sampah (with Submenu) --}}
            <li class="mobile-menu-item">
                <a href="javascript:void(0)" class="mobile-menu-link" data-submenu="bank-sampah">
                    <i class="fas fa-recycle"></i>
                    <span>Bank Sampah</span>
                    <i class="fas fa-chevron-right mobile-submenu-toggle"></i>
                </a>
                <ul class="mobile-submenu">
                    <li><a href="{{ route('admin.bank-sampah.setor.index') }}" class="mobile-menu-link">
                            <i class="fas fa-upload"></i> Data Setor
                        </a></li>
                    <li><a href="{{ route('admin.bank-sampah.penarikan.index') }}" class="mobile-menu-link">
                            <i class="fas fa-money-bill-wave"></i> Data Penarikan
                            @if ($penarikanCount > 0)
                                <span class="mobile-menu-badge">{{ $penarikanCount }}</span>
                            @endif
                        </a></li>
                    <li><a href="{{ route('admin.bank-sampah.jenis-sampah.index') }}" class="mobile-menu-link">
                            <i class="fas fa-tags"></i> Jenis & Harga Sampah
                        </a></li>
                    <li><a href="{{ route('admin.bank-sampah.penjemputan.index') }}" class="mobile-menu-link">
                            <i class="fas fa-truck"></i> Penjemputan
                        </a></li>
                </ul>
            </li>

            {{-- Artikel Edukasi --}}
            <li class="mobile-menu-item">
                <a href="{{ route('admin.artikel.index') }}"
                    class="mobile-menu-link {{ request()->routeIs('admin.artikel.*') ? 'active' : '' }}">
                    <i class="fas fa-newspaper"></i>
                    <span>Artikel Edukasi</span>
                </a>
            </li>

            {{-- Informasi TPS --}}
            <li class="mobile-menu-item">
                <a href="{{ route('admin.tps.index') }}"
                    class="mobile-menu-link {{ request()->routeIs('admin.tps.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Informasi TPS</span>
                </a>
            </li>

            {{-- Data Pengguna --}}
            <li class="mobile-menu-item">
                <a href="{{ route('admin.data-pengguna.index') }}"
                    class="mobile-menu-link {{ request()->routeIs('admin.data-pengguna.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Data Pengguna</span>
                </a>
            </li>

            {{-- Kelola Akun --}}
            <li class="mobile-menu-item">
                <a href="{{ route('admin.akun.index') }}"
                    class="mobile-menu-link {{ request()->routeIs('admin.akun.*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Kelola Akun</span>
                </a>
            </li>

            {{-- Logout --}}
            <li class="mobile-menu-item mobile-menu-logout">
                <a href="{{ route('admin.logout') }}" class="mobile-menu-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

{{-- Logout Form --}}
<form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
    @csrf
</form>
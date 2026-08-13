@extends('layouts.app')

@section('title', 'RESIK - Dinas Lingkungan Hidup Kabupaten Nganjuk')

@section('content')
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <!-- KIRI: Logo dan Teks -->
            <div class="navbar-logo">
                <img src="{{ asset('assets/logo-dlh.png') }}" alt="Logo DLH Nganjuk">
                <div class="logo-text">
                    <span>DINAS LINGKUNGAN HIDUP</span>
                    <span>KABUPATEN NGANJUK</span>
                </div>
            </div>

            <!-- KANAN: Tombol Hamburger (Toggle) -->
            <div class="navbar-toggle" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <!-- Menu Navigasi -->
            <div class="navbar-menu">
                <a href="#beranda" class="nav-link">BERANDA</a>
                <a href="#tentang" class="nav-link">TENTANG KAMI</a>
                <a href="#fitur" class="nav-link">FITUR</a>
                <a href="#jenis" class="nav-link">JENIS SAMPAH</a>
                <a href="#download" class="nav-link">DOWNLOAD APK</a>
                <a href="{{ route('admin.login') }}" class="btn-login">
                    <img src="{{ asset('assets/button-login.png') }}" alt="Login">
                    LOGIN ADMIN
                </a>
            </div>
        </div>
    </nav>

    <!-- Section 1: Hero/Landing Page -->
    <section class="hero-section" id="beranda">
        <!-- Background Image -->
        <div class="hero-bg">
            <video autoplay muted loop playsinline>
                <source src="{{ asset('assets/landingpage.mp4') }}" type="video/mp4">
            </video>
            <div class="hero-overlay"></div>
        </div>

        <!-- Content -->
        <div class="hero-content container">
            <div class="hero-text">
                <h1 class="hero-title" data-text="Kelola Lingkungan Untuk Nganjuk yang Lebih Bersih & Asri">
                    <span class="title-line">Kelola Lingkungan</span><br>
                    <span class="title-line">Untuk Nganjuk yang</span><br>
                    <span class="title-line text-green">Lebih Bersih & Asri</span>
                </h1>
                <p class="hero-subtitle">
                    Bersama RESIK, wujudkan Nganjuk yang bersih<br>
                    dan asri untuk generasi mendatang
                </p>
                <div class="hero-buttons">
                    <a href="#tentang" class="btn btn-primary">Lihat Selengkapnya</a>
                    <a href="#download" class="btn btn-secondary">Laporkan Sampah</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ✅ WRAPPER: Section 2 s/d 5 (Background Daun) -->
    <div class="leaf-bg-wrapper">
        <!-- Section 2: Tentang Kami -->
        <section class="section about-section" id="tentang">
            <div class="content about-content-flex">
                <!-- Logo Icon di Kiri -->
                <div class="about-icon">
                    <img src="{{ asset('assets/logo-resik.png') }}" alt="Logo RESIK R">
                </div>

                <!-- Teks di Kanan -->
                <div class="about-text">
                    <h2 class="about-title">Hallo, Sobat RESIK!</h2>
                    <p class="about-description">
                        RESIK hadir sebagai wadah kolaborasi antara masyarakat dan pemerintah Kabupaten Nganjuk untuk
                        menciptakan aksi yang berdampak bagi lingkungan. Melalui aplikasi ini, warga Nganjuk bisa langsung
                        melaporkan titik sampah ilegal sekaligus mengubah sampah terpilah menjadi nilai ekonomi lewat fitur
                        Bank Sampah. Mari bersama RESIK, kita jaga bumi dan wujudkan gaya hidup berkelanjutan yang lebih
                        bermakna.
                    </p>
                </div>
            </div>
        </section>

        <!-- Section 3: Fitur -->
        <section class="section features-section" id="fitur">
            <div class="content">
                <h2 class="section-title">FITUR</h2>
                <div class="features-grid">
                    <!-- Fitur 1: Lapor Sampah Ilegal -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="{{ asset('assets/fitur-sampah-ilegal.png') }}" alt="Lapor Sampah Ilegal">
                        </div>
                        <h3 class="feature-title">LAPOR SAMPAH ILEGAL</h3>
                        <p class="feature-description">
                            Bagikan foto sampah ke Aplikasi Resik, dan adukan keluhan sampah yang ada di sekitarmu.
                        </p>
                    </div>

                    <!-- Fitur 2: Laporan Bank Sampah -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="{{ asset('assets/fitur-laporan-bank-sampah.png') }}" alt="Bank Sampah">
                        </div>
                        <h3 class="feature-title">LAPORAN BANK SAMPAH</h3>
                        <p class="feature-description">
                            Kelola setoran sampah daur ulang, cek poin/nilai tabungan sampah, dan pantau transaksi bank
                            sampah masyarakat.
                        </p>
                    </div>

                    <!-- Fitur 3: Artikel Edukasi -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="{{ asset('assets/fitur-artikel-edukasi.png') }}" alt="Artikel Edukasi">
                        </div>
                        <h3 class="feature-title">ARTIKEL EDUKASI</h3>
                        <p class="feature-description">
                            Dapatkan informasi, tips pengelolaan sampah, daur ulang, kebersihan lingkungan, dan gaya hidup
                            ramah lingkungan.
                        </p>
                    </div>

                    <!-- Fitur 4: Informasi TPS -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="{{ asset('assets/fitur-informasi-tps.png') }}" alt="Informasi TPS">
                        </div>
                        <h3 class="feature-title">INFORMASI TPS</h3>
                        <p class="feature-description">
                            Temukan lokasi Tempat Pembuangan Sementara (TPS) terdekat, jadwal angkut, dan informasi
                            kapasitas TPS di wilayah Anda.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 4: Jenis Sampah -->
        <section class="section waste-section" id="jenis">
            <div class="content">
                <h2 class="section-title">JENIS <span class="text-green">SAMPAH</span></h2>

                <!-- Tabs -->
                <div class="waste-tabs">
                    <button class="waste-tab active" onclick="showWasteType('organik')">
                        <img src="{{ asset('assets/jenis-organik.png') }}" alt="Organik">
                        Organik
                    </button>
                    <button class="waste-tab" onclick="showWasteType('non-organik')">
                        <img src="{{ asset('assets/jenis-non-organik.png') }}" alt="Non Organik">
                        Non Organik
                    </button>
                </div>

                <!-- Organik Waste -->
                <div class="waste-content active" id="organik">
                    <div class="waste-grid">
                        <div class="waste-item">
                            <img src="{{ asset('assets/jenis-organik-kertas.png') }}" alt="Kertas">
                            <h4>KERTAS</h4>
                            <p>Contoh: Kertas, Koran, dan Karton</p>
                        </div>
                        <div class="waste-item">
                            <img src="{{ asset('assets/jenis-organik-kayu.png') }}" alt="Kayu">
                            <h4>KAYU</h4>
                            <p>Contoh: Ranting, kayu, dan serpihan kayu</p>
                        </div>
                        <div class="waste-item">
                            <img src="{{ asset('assets/jenis-organik-buah.png') }}" alt="Buah & Sayur">
                            <h4>BUAH & SAYUR</h4>
                            <p>Contoh: Sisa makanan, buah, dan sayur</p>
                        </div>
                    </div>
                </div>

                <!-- Non-Organik Waste -->
                <div class="waste-content" id="non-organik">
                    <div class="waste-grid">
                        <div class="waste-item">
                            <img src="{{ asset('assets/jenis-non-logam.png') }}" alt="Logam">
                            <h4>LOGAM</h4>
                            <p>Contoh: Kaleng, besi, dan alumunium</p>
                        </div>
                        <div class="waste-item">
                            <img src="{{ asset('assets/jenis-non-plastik.png') }}" alt="Plastik">
                            <h4>PLASTIK</h4>
                            <p>Contoh: Botol plastik bekas, kantong plastik, dan sedotan</p>
                        </div>
                        <div class="waste-item">
                            <img src="{{ asset('assets/jenis-non-kaca.png') }}" alt="Kaca">
                            <h4>KACA</h4>
                            <p>Contoh: Botol kaca, gelas, dan serpihan kaca</p>
                        </div>
                        <div class="waste-item">
                            <img src="{{ asset('assets/minyak_jelantah2.png') }}" alt="Minyak">
                            <h4>MINYAK JELANTAH</h4>
                            <p>Contoh: Minyak Sayur, Minyak Sawit</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 5: Download APK -->
        <section class="section download-section" id="download">
            <div class="content">
                <h2 class="download-title">Download Aplikasi</h2>
                <p class="download-description">
                    Nikmati kemudahan layanan pengelolaan sampah dan Bank Sampah dalam satu aplikasi digital yang cepat,
                    efisien, dan ramah pengguna.
                </p>
                
                <a href="{{ asset('downloads_apk/resik.apk') }}" class="btn-download" download>
                    DOWNLOAD APK
                </a>
            </div>
        </section>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>NGANJUK RESIK</h3>
                <p>Pengelolaan sampah di Kabupaten Nganjuk kini lebih mudah dan modern bersama Resik.
                    Mendukung layanan Bank Sampah untuk mewujudkan lingkungan Nganjuk yang bersih, tertata, dan
                    berkelanjutan.</p>
            </div>
            <div class="footer-section text">
                <h4>RESIK</h4>
                <ul>
                    <li><a href="#beranda">Beranda</a></li>
                    <li><a href="#tentang">Tentang Kami</a></li>
                    <li><a href="#fitur">Fitur</a></li>
                    <li><a href="#jenis">Jenis Sampah</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Social Media</h4>
                <div class="social-icons">
                    <!-- Instagram -->
                    <a href="https://www.instagram.com/dlhnganjuk/" target="_blank">
                        <img src="{{ asset('assets/instagram.png') }}" alt="Instagram">
                    </a>

                    <!-- YouTube -->
                    <a href="https://www.youtube.com/@dlhbisa" target="_blank">
                        <img src="{{ asset('assets/youtube.png') }}" alt="YouTube">
                    </a>

                    <!-- Lokasi (Maps) -->
                    <a href="https://maps.app.goo.gl/xTCboDN5KfbMNJTQ8" target="_blank">
                        <img src="{{ asset('assets/maps.png') }}" alt="Lokasi">
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Dinas Lingkungan Hidup Kabupaten Nganjuk. All rights reserved.</p>
        </div>
    </footer>
@endsection

@push('styles')
    {{-- FUNGSI: Memuat file CSS eksternal untuk landing page --}}
    <link rel="stylesheet" href="{{ asset('css/landing.css?v=' . time()) }}">
@endpush

@push('scripts')
    {{-- FUNGSI: Memuat file JS eksternal untuk interaksi landing page --}}
    <script src="{{ asset('js/landing.js?v=' . time()) }}"></script>
@endpush
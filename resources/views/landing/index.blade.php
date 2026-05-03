@extends('layouts.app')

@section('title', 'RESIK - Dinas Lingkungan Hidup Kabupaten Nganjuk')

@section('content')
<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-logo">
            <img src="{{ asset('assets/logo-dlh.png') }}" alt="Logo DLH Nganjuk">
            <div class="logo-text">
                <span>DINAS LINGKUNGAN HIDUP</span>
                <span>KABUPATEN NGANJUK</span>
            </div>
        </div>
        <div class="navbar-menu">
            <a href="#beranda" class="nav-link">BERANDA</a>
            <a href="#tentang" class="nav-link">TENTANG KAMI</a>
            <a href="#fitur" class="nav-link">FITUR</a>
            <a href="#jenis" class="nav-link">JENIS SAMPAH</a>
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
        <!-- Ganti 'background-hero.png' dengan nama file gambar gedung kamu -->
        <img src="{{ asset('assets/background-landing.png') }}" alt="Dinas Lingkungan Hidup Nganjuk">
        <div class="hero-overlay"></div>
    </div>

    <!-- Content -->
    <div class="hero-content container">
        <div class="hero-text">
            <h1 class="hero-title">
                Kelola Lingkungan<br>
                Untuk Nganjuk yang<br>
                <span class="text-green">Lebih Bersih & Asri</span>
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
                    RESIK (Sistem Pelaporan Sampah Ilegal) hadir sebagai jembatan antara masyarakat, pemerintah, dan pegiat lingkungan. Kami percaya bahwa perubahan besar dimulai dari tindakan kecil. Melalui aplikasi ini, Anda bisa langsung melaporkan titik pembuangan sampah ilegal, sementara fitur Bank Sampah membantu Anda menukar sampah terpilah menjadi nilai ekonomi. Bersama, kita jaga lingkungan, tingkatkan kesadaran, dan wujudkan gaya hidup berkelanjutan.
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
                        Kelola setoran sampah daur ulang, cek poin/nilai tabungan sampah, dan pantau transaksi bank sampah masyarakat.
                    </p>
                </div>

                <!-- Fitur 3: Artikel Edukasi -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('assets/fitur-artikel-edukasi.png') }}" alt="Artikel Edukasi">
                    </div>
                    <h3 class="feature-title">ARTIKEL EDUKASI</h3>
                    <p class="feature-description">
                        Dapatkan informasi, tips pengelolaan sampah, daur ulang, kebersihan lingkungan, dan gaya hidup ramah lingkungan.
                    </p>
                </div>

                <!-- Fitur 4: Informasi TPS -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('assets/fitur-informasi-tps.png') }}" alt="Informasi TPS">
                    </div>
                    <h3 class="feature-title">INFORMASI TPS</h3>
                    <p class="feature-description">
                        Temukan lokasi Tempat Pembuangan Sementara (TPS) terdekat, jadwal angkut, dan informasi kapasitas TPS di wilayah Anda.
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
                </div>
            </div>
        </div>
    </section>

    <!-- Section 5: Download APK -->
    <section class="section download-section" id="download">
        <div class="download-background">
            <img src="{{ asset('assets/leaf-pattern.png') }}" alt="Background Pattern" class="pattern-image">
        </div>
        <div class="content">
            <h2 class="download-title">Download Aplikasi</h2>
            <p class="download-description">
                RESIK adalah platform yang memudahkan pelaporan sampah ilegal dengan perangkat mobile/smartphone yang bisa diakses online.
            </p>
            <a href="https://drive.google.com/file/d/1RJLPEwUK9LQbHHdUTWy_asPKgr3FeJ9E/view?usp=sharing" class="btn-download" target="_blank">
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
            <p>Berawal dari foto, Berakhir pada Kelestarian. Langkah kecil memberikan dampak besar pada pelestarian lingkungan.</p>
        </div>
        <div class="footer-section">
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
                <a href="https://www.instagram.com/dlhnganjuk/" target="_blank">
                    <img src="{{ asset('assets/instagram.png') }}" alt="Instagram">
                </a>
                <a href="https://www.facebook.com/profile.php?id=100076050218713" target="_blank">
                    <img src="{{ asset('assets/facebook.png') }}" alt="Facebook">
                </a>
                <a href="https://www.youtube.com/@dlhbisa" target="_blank">
                    <img src="{{ asset('assets/youtube.png') }}" alt="YouTube">
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
<style>
    /* Navbar Styles */
    .navbar {
        background: #2e8b57;
        padding: 15px 0;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .navbar-logo {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .navbar-logo img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }

    .logo-text {
        display: flex;
        flex-direction: column;
        color: white;
        font-weight: bold;
        font-size: 14px;
        line-height: 1.2;
    }

    .navbar-menu {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .nav-link {
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: opacity 0.3s;
    }

    .nav-link:hover {
        opacity: 0.8;
    }

    .btn-login {
        background: #ff6347;
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s;
    }

    .btn-login:hover {
        background: #ff4500;
    }

    .btn-login img {
        width: 20px;
        height: 20px;
    }

    /* Section Base Styles */
    /* ✅ LEAF BACKGROUND WRAPPER (Section 2-5) */
    .leaf-bg-wrapper {
        background-image: url('{{ asset("assets/background-page.png") }}');
        background-size: 100% 100%;
        /* Gambar membentang penuh dari atas section 2 sampai bawah section 5 */
        background-position: top center;
        background-repeat: no-repeat;
        position: relative;
    }

    /* Overlay putih transparan agar teks tetap terbaca di atas gambar daun */
    .leaf-bg-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0);
        /* Ubah opacity 0.80-0.95 sesuai selera */
        z-index: 0;
    }

    /* Pastikan konten section berada di atas overlay & background */
    .leaf-bg-wrapper .section,
    .leaf-bg-wrapper .content {
        position: relative;
        z-index: 1;
        background: transparent !important;
        /* Hapus background putih bawaan section */
    }

    .section {
        padding: 80px 20px;
        position: relative;
    }

    .content {
        max-width: 1200px;
        margin: 0 auto;
    }

    .section-title {
        text-align: center;
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 50px;
        color: #1a1a1a;
    }

    .text-green {
        color: #2e8b57;
    }

    /* Hero Section Styling */
    .hero-section {
        position: relative;
        width: 100%;
        height: 100vh;
        /* Full layar */
        display: flex;
        align-items: center;
        /* Teks di tengah secara vertikal */
        overflow: hidden;
    }

    /* Background Image Container */
    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }

    .hero-bg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* Agar gambar tidak gepeng */
        object-position: center;
    }

    /* Optional: Overlay tipis agar teks lebih terbaca jika gambar terlalu terang/gelap */
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.2);
        /* Ubah opacity jika perlu */
    }

    /* Content Wrapper */
    .hero-content {
        position: relative;
        z-index: 1;
        max-width: 1200px;
        width: 100%;
        margin: 0 auto;
        padding: 0 20px;
        padding-top: 60px;
        /* Kompensasi Navbar Fixed */
    }

    .hero-text {
        /* Background transparan (tidak ada kotak putih) */
        background: transparent;
        backdrop-filter: blur(5px);


        /* Hapus efek card */
        padding: 20px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        border: 1px solid (0, 0, 0, 0.3);

        /* Posisi & Ukuran */
        max-width: 600px;
        text-align: left;
        color: white;
        /* Ubah warna teks jadi putih agar kontras */
    }

    /* Typography (Ditambah Text Shadow agar terbaca di background terang/gelap) */
    .hero-title {
        font-size: clamp(28px, 5vw, 50px);
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 20px;
        color: white;
        /* Teks putih */
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
        /* Bayangan hitam agar teks jelas */
    }

    .hero-subtitle {
        font-size: clamp(16px, 2vw, 20px);
        color: #f0f0f0;
        /* Putih agak abu */
        margin-bottom: 30px;
        line-height: 1.5;
        font-weight: 500;
        text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
        /* Bayangan teks */
    }

    .text-green {
        color: #2e8b57;
        /* Hijau khas RESIK */
    }

    /* Buttons */
    .hero-buttons .btn {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .btn {
        padding: 12px 28px;
        border-radius: 50px;
        /* Tombol bulat lonjong */
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        display: inline-block;
    }

    .btn-primary {
        background: #2e8b57;
        color: white;
        box-shadow: 0 4px 10px rgba(46, 139, 87, 0.3);
    }

    .btn-primary:hover {
        background: #247a46;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #1b5e20;
        /* Hijau lebih tua */
        color: white;
        box-shadow: 0 4px 10px rgba(27, 94, 32, 0.3);
    }

    .btn-secondary:hover {
        background: #144a18;
        transform: translateY(-2px);
    }

    /* Section 2: Tentang Kami Styles */
    .about-section {
        background: transparent !important;
        /* Pastikan transparan agar background wrapper terlihat */
        padding-top: 40px;
        padding-bottom: 40px;
    }

    /* Container Flex untuk Icon dan Teks */
    .about-content-flex {
        display: flex;
        align-items: flex-start;
        /* Icon dan teks mulai dari atas */
        justify-content: center;
        gap: 30px;
        /* Jarak antara icon dan teks */
        max-width: 900px;
        /* Batasi lebar konten agar rapi */
        margin: 0 auto;
        text-align: left;
        /* Teks rata kiri seperti di gambar */
    }

    .about-icon {
        flex-shrink: 0;
        /* Icon tidak mengecil */
    }

    .about-icon img {
        width: 60%;
        /* Ukuran icon R (sesuaikan jika perlu) */
        height: auto;
    }

    .about-title {
        font-size: 30px;
        font-weight: 800;
        color: #2e8b57;
        /* Hijau RESIK */
        margin-bottom: 15px;
        margin-top: 0;
        text-align: left;
    }

    .about-description {
        font-size: 16px;
        font-weight: 400;
        line-height: 1.7;
        color: #444;
        margin: 0;
    }

    /* Features Section */
    .features-section {
        background: white;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .feature-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }

    .feature-icon {
        margin-bottom: 20px;
    }

    .feature-icon img {
        width: 200px;
        height: 200px;
        object-fit: contain;
    }

    .feature-title {
        font-size: 18px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 15px;
    }

    .feature-description {
        font-size: 14px;
        color: #666;
        line-height: 1.6;
    }

    /* Waste Section */
    .waste-section {
        background: #f9fff9;
    }

    .waste-tabs {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 40px;
    }

    .waste-tab {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 30px;
        border: none;
        border-radius: 25px;
        background: #e0e0e0;
        color: #333;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }

    .waste-tab.active {
        background: #2e8b57;
        color: white;
    }

    .waste-tab img {
        width: 40px;
        height: 40px;
    }

    .waste-content {
        display: none;
    }

    .waste-content.active {
        display: block;
    }

    .waste-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
    }

    .waste-item {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.3s;
    }

    .waste-item:hover {
        transform: translateY(-5px);
    }

    .waste-item img {
        width: 180px;
        height: 180px;
        object-fit: contain;
        margin-bottom: 15px;
    }

    .waste-item h4 {
        font-size: 18px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 10px;
    }

    .waste-item p {
        font-size: 13px;
        color: #666;
        line-height: 1.5;
    }

    /* Download Section */
    .download-section {
        background: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .download-background {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.1;
    }

    .pattern-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .download-title {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #1a1a1a;
    }

    .download-description {
        max-width: 600px;
        margin: 0 auto 30px;
        font-size: 16px;
        color: #666;
        line-height: 1.6;
    }

    .btn-download {
        background: #2e8b57;
        color: white;
        padding: 15px 40px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        font-size: 16px;
        display: inline-block;
        transition: all 0.3s;
    }

    .btn-download:hover {
        background: #226b42;
        transform: translateY(-2px);
    }

    /* Footer */
    .footer {
        background: #004d26;
        color: white;
        padding: 50px 20px 20px;
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 30px;
    }

    .footer-section h3,
    .footer-section h4 {
        margin-bottom: 15px;
        font-size: 18px;
    }

    .footer-section p {
        font-size: 14px;
        line-height: 1.6;
        opacity: 0.9;
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-section ul li {
        margin-bottom: 8px;
    }

    .footer-section ul li a {
        color: white;
        text-decoration: none;
        font-size: 14px;
        opacity: 0.9;
        transition: opacity 0.3s;
    }

    .footer-section ul li a:hover {
        opacity: 1;
    }

    .social-icons {
        display: flex;
        gap: 15px;
    }

    .social-icons a img {
        width: 30px;
        height: 30px;
        transition: transform 0.3s;
    }

    .social-icons a:hover img {
        transform: scale(1.1);
    }

    .footer-bottom {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 14px;
        opacity: 0.8;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .navbar-container {
            flex-direction: column;
            gap: 15px;
        }

        .navbar-menu {
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .hero-content {
            flex-direction: column;
        }

        .hero-text {
            max-width: 100%;
            /* Full width di HP */
            padding: 30px 20px;
            /* Padding lebih kecil */
            margin-top: 20px;
            /* Jarak dari gambar jika layout berubah */
        }

        .hero-title {
            font-size: 28px;
            /* Ukuran font lebih kecil di HP */
        }

        .hero-subtitle {
            font-size: 14px;
        }

        .hero-buttons {
            justify-content: center;
        }

        .about-content-flex {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }

        .about-icon {
            display: flex;
            justify-content: center;
        }

        .about-icon img {
            width: 70px;
        }

        .about-title,
        .about-description {
            text-align: center;
        }

        .features-grid,
        .waste-grid {
            grid-template-columns: 1fr;
        }

        .waste-tabs {
            flex-direction: column;
            align-items: center;
        }

        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .social-icons {
            justify-content: center;
        }

        .footer-bottom p{
            color: white;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Waste Type Tabs
    function showWasteType(type) {
        // Hide all content
        document.querySelectorAll('.waste-content').forEach(content => {
            content.classList.remove('active');
        });

        // Remove active class from all tabs
        document.querySelectorAll('.waste-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected content
        document.getElementById(type).classList.add('active');

        // Add active class to clicked tab
        event.target.closest('.waste-tab').classList.add('active');
    }

    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endpush

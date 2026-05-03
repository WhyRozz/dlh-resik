<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIMPELSI - Dashboard Umum')</title>
    <link rel="shortcut icon" href="{{ asset('assets/logo_simpelsi.png') }}" type="image/x-icon">
    <style>
        /* =============== RESET & GLOBAL STYLES =============== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* =============== NAVBAR DESKTOP =============== */
        .navbar-desktop {
            background: #2e8b57;
            color: white;
            padding: 12px 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0; left: 0; z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .nav-logo-container { display: flex; align-items: center; gap: 10px; }
        .nav-logo {
            width: 40px; height: 40px; border-radius: 50%;
            background: white; display: flex; align-items: center; justify-content: center;
            font-weight: bold; color: #2e8b57;
        }
        .nav-logo img { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; }
        .nav-title { font-size: 18px; font-weight: bold; }
        .nav-menu { display: flex; gap: 20px; align-items: center; }
        .nav-menu a, .nav-menu .nav-login {
            color: white; text-decoration: none; font-size: 14px; font-weight: 500;
            padding: 8px 12px; border-radius: 4px; transition: background 0.2s, color 0.2s;
            text-align: center; white-space: nowrap; display: flex; justify-content: center; align-items: center;
        }
        .nav-menu a:hover { background: rgba(255,255,255,0.1); color: #e0f0e9; }
        .nav-menu .nav-login {
            background: #ff6347; color: white; padding: 8px 16px;
            border-radius: 20px; font-weight: bold;
        }
        .nav-menu .nav-login:hover { background: #ff4500; }

        /* =============== NAVBAR MOBILE =============== */
        .navbar-mobile {
            background: #2e8b57; color: white; padding: 18px 20px;
            width: 100%; display: none; justify-content: space-between; align-items: center;
            position: fixed; top: 0; left: 0; z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 70px;
        }
        .nav-mobile-menu-btn {
            background: none; border: none; color: white; font-size: 24px; cursor: pointer;
            display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 3px;
        }
        .nav-mobile-menu-btn span {
            display: block; width: 28px; height: 3px; background: white;
            transition: all 0.3s ease; border-radius: 2px;
        }
        .nav-mobile-menu-btn.active span:nth-child(1) { transform: rotate(45deg) translate(6px, 6px); }
        .nav-mobile-menu-btn.active span:nth-child(2) { opacity: 0; }
        .nav-mobile-menu-btn.active span:nth-child(3) { transform: rotate(-45deg) translate(6px, -6px); }

        .nav-mobile-menu {
            position: fixed; top: 0; right: 0; width: 320px; height: 100vh;
            background: linear-gradient(135deg, #2e8b57, #1e6b3f);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            box-shadow: -5px 0 20px rgba(0,0,0,0.1); z-index: 999;
            padding-top: 90px; padding-bottom: 20px; overflow-y: auto;
            display: flex; flex-direction: column; align-items: stretch;
            transform: translateX(100%); opacity: 0; visibility: hidden;
            transition: transform 0.4s cubic-bezier(0.68,-0.55,0.265,1.55), opacity 0.3s ease, visibility 0.3s;
        }
        .nav-mobile-menu.active { transform: translateX(0); opacity: 1; visibility: visible; }
        .nav-mobile-menu a, .nav-mobile-menu .nav-login {
            color: white; text-decoration: none; padding: 16px 30px;
            font-size: 16px; font-weight: 500; display: block;
            transition: background 0.2s, transform 0.2s; border-left: 3px solid transparent;
        }
        .nav-mobile-menu a:hover, .nav-mobile-menu .nav-login:hover {
            background: rgba(255,255,255,0.1); border-left: 3px solid white; transform: translateX(5px);
        }
        .nav-mobile-menu .nav-login {
            background: #ff6347; margin: 25px 30px; text-align: center;
            border-radius: 25px; font-weight: bold; padding: 14px 20px; font-size: 16px;
        }
        .nav-mobile-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.3); z-index: 998;
            opacity: 0; visibility: hidden; transition: all 0.3s ease;
        }
        .nav-mobile-overlay.active { opacity: 1; visibility: visible; }

        /* =============== SECTION STYLES =============== */
        .section {
            min-height: 100vh; width: 100%;
            padding: clamp(30px, 5vw, 60px) clamp(15px, 4vw, 40px);
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            position: relative; background: white; padding-top: 80px;
        }
        @media (max-width: 768px) { .section { padding-top: 70px; } }

        .content { max-width: 1200px; width: 100%; text-align: center; margin-top: 0; }
        h1 { color: #2e8b57; margin-bottom: 20px; font-size: clamp(20px, 4vw, 28px); }
        p { line-height: 1.6; margin-bottom: 20px; color: #333; font-size: clamp(14px, 1.8vw, 16px); }
        .btn-green {
            background: #2e8b57; color: white; padding: 10px 20px; border-radius: 25px;
            text-decoration: none; font-weight: bold; display: inline-block;
            margin-top: 15px; transition: background 0.2s; font-size: clamp(13px, 1.8vw, 15px);
        }
        .btn-green:hover { background: #226b42; }

        /* =============== HOME SECTION =============== */
        .home-content { display: flex; align-items: center; gap: 40px; flex-wrap: wrap; }
        .home-text { flex: 1; min-width: 300px; }
        .home-image { flex: 1; min-width: 300px; text-align: center; position: relative; overflow: hidden; }
        .home-image img {
            max-width: 100%; height: auto; border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            animation: waveImage 4s ease-in-out infinite; transform-origin: bottom; display: block;
        }

        /* =============== PROFIL SECTION =============== */
        .profil-content { display: flex; align-items: center; gap: 40px; flex-wrap: wrap; }
        .profil-text { flex: 1; min-width: 300px; }
        .profil-logo { display: flex; flex-direction: column; align-items: center; gap: 15px; min-width: 300px; }
        .logo-large-container { width: 100%; text-align: center; }
        .logo-large {
            max-width: clamp(130px, 20vw, 250px); height: auto;
            border-radius: 50%; box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .logo-small-container { margin-top: -10px; margin-left: 5px; }
        .logo-small { width: clamp(35px, 8vw, 70px); height: auto; }

        /* =============== VISI & MISI SECTION =============== */
        .visimisi-content {
            display: flex; flex-direction: column; justify-content: space-between;
            align-items: center; gap: 20px; padding: 0 20px; width: 100%;
        }
        .visimisi-columns { display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; width: 100%; }
        .visimisi-column {
            flex: 1; min-width: 280px; background: #f9f9f9; padding: 25px;
            border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            border-left: 4px solid #2e8b57;
        }
        .visimisi-column h2 { color: #2e8b57; font-size: clamp(18px, 3vw, 22px); margin-bottom: 15px; }

        /* =============== FITUR SECTION =============== */
        .features { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; margin-top: 30px; }
        .feature-card {
            background: #e6f2e6; padding: 20px; border-radius: 12px;
            width: 100%; max-width: 300px; text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .feature-icon {
            width: 100px; height: 100px; margin: 0 auto 15px;
            color: white; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-size: 24px;
        }
        .feature-icon img { width: 100px; height: 100px; }
        .feature-title { font-size: 16px; color: #2e8b57; margin-bottom: 10px; font-weight: bold; }

        /* =============== JENIS SAMPAH SECTION =============== */
        .waste-types-container { display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; margin-top: 30px; }
        .waste-category {
            background: #e6e6e6; padding: 20px; border-radius: 12px;
            width: 100%; max-width: 500px; text-align: center;
        }
        .waste-category-title { color: #2e8b57; font-size: 18px; margin-bottom: 15px; font-weight: bold; }
        .waste-items { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .waste-item {
            background: white; padding: 10px; border-radius: 8px; text-align: center;
            border: 1px solid #ddd; display: flex; flex-direction: column;
            align-items: center; justify-content: center; min-width: 80px;
        }
        .waste-icon { font-size: 24px; margin-bottom: 5px; color: #2e8b57; }
        .waste-name { font-weight: bold; color: #2e8b57; font-size: 12px; }

        /* =============== DOWNLOAD SECTION =============== */
        .download-section-content {
            display: flex; flex-direction: column; align-items: center;
            width: 100%; max-width: 1000px; gap: 40px;
        }
        .download-text { text-align: center; max-width: 600px; }
        .download-btn {
            background: #2e8b57; color: white; padding: 12px 24px; border-radius: 25px;
            font-weight: bold; font-size: 16px; text-decoration: none;
            display: inline-block; margin-top: 20px; transition: background 0.2s;
        }
        .download-btn:hover { background: #226b42; }

        /* =============== ANIMASI =============== */
        @keyframes waveImage {
            0% { transform: scaleY(1) skewY(0deg); }
            50% { transform: scaleY(1.03) skewY(3deg); }
            100% { transform: scaleY(1) skewY(0deg); }
        }

        /* =============== FOOTER =============== */
        .footer-bottom {
            background: #004d26; color: white; padding: 30px 20px;
            margin-top: 40px; opacity: 0; transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
            position: relative; z-index: 5;
        }
        .footer-bottom.visible { opacity: 1; transform: translateY(0); }
        .footer-container {
            max-width: 1200px; margin: 0 auto; display: flex;
            flex-wrap: wrap; gap: 30px; justify-content: space-between;
        }
        .footer-col { flex: 1; min-width: 250px; }
        .footer-col h3 { font-size: 18px; margin-bottom: 15px; font-weight: bold; }
        .footer-col p { line-height: 1.6; font-size: 14px; margin-bottom: 10px; }
        .footer-col .copyright { font-size: 12px; opacity: 0.8; margin-top: 15px; }
        .footer-col ul { list-style: none; padding: 0; margin: 0; }
        .footer-col ul li { margin-bottom: 8px; }
        .footer-col ul li a {
            color: white; text-decoration: none; font-size: 14px; transition: color 0.2s;
        }
        .footer-col ul li a:hover { color: #ffeb3b; }
        .social-icons { display: flex; gap: 15px; margin-top: 10px; }
        .social-icons a img { width: 24px; height: 24px; filter: brightness(0) invert(1); }

        /* =============== RESPONSIVE =============== */
        @media (max-width: 768px) {
            .navbar-desktop { display: none; }
            .navbar-mobile { display: flex; }
            .nav-mobile-menu.active { display: flex; }
            .nav-logo-container { order: 1; }
            .nav-mobile-menu-btn { order: 2; }
            .nav-mobile-menu { right: 0px; }
            .navbar-mobile { padding: 10px 15px; }
            .nav-logo { width: 30px; height: 30px; }
            .nav-title { font-size: 16px; }
            .content { padding: 20px; }
            .home-content, .profil-content { flex-direction: column; text-align: center; }
            .home-image, .profil-logo { order: -1; }
            .waste-types-container { flex-direction: column; }
            .waste-category { max-width: 100%; }
        }
        @media (max-width: 480px) {
            .navbar-mobile { padding: 8px; }
            .nav-logo { width: 24px; height: 24px; }
            .nav-title { font-size: 14px; }
            .nav-mobile-menu { width: 180px; }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Navbar Desktop -->
    <div class="navbar-desktop">
        <div class="nav-logo-container">
            <div class="nav-logo">
                <img src="{{ asset('assets/logo.jpg') }}" alt="Logo SIMPELSI">
            </div>
            <div class="nav-title">SimpelSi</div>
        </div>
        <div class="nav-menu">
            <a href="#home">Beranda</a>
            <a href="#profil">Profil</a>
            <a href="#fitur">Fitur</a>
            <a href="#jenis">Jenis Sampah</a>
            <a href="#download">Pengaduan Laporan</a>
            <a href="{{ route('admin.login') }}" class="nav-login">Login</a>
        </div>
    </div>

    <!-- Navbar Mobile -->
    <div class="navbar-mobile">
        <div class="nav-logo-container">
            <div class="nav-logo">
                <img src="{{ asset('assets/logo.jpg') }}" alt="Logo SIMPELSI">
            </div>
            <div class="nav-title">SimpelSi</div>
        </div>
        <button class="nav-mobile-menu-btn" id="mobileMenuBtn">
            <span></span><span></span><span></span>
        </button>
    </div>

    <!-- Menu Mobile -->
    <div class="nav-mobile-menu" id="mobileMenu">
        <a href="#home">Beranda</a>
        <a href="#profil">Profil</a>
        <a href="#fitur">Fitur</a>
        <a href="#jenis">Jenis Sampah</a>
        <a href="#download">Pengaduan Laporan</a>
        <a href="{{ route('admin.login') }}" class="nav-login">Login</a>
    </div>

    <!-- Overlay -->
    <div class="nav-mobile-overlay" id="mobileOverlay"></div>

    <!-- Konten Utama -->
    <main>
        @yield('content')
    </main>

    

    <script>
        // Toggle Mobile Menu & Overlay
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileOverlay = document.getElementById('mobileOverlay');

        mobileMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            mobileMenu.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            mobileMenuBtn.classList.toggle('active');
        });

        mobileOverlay.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
        });

        document.querySelectorAll('#mobileMenu a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                mobileOverlay.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
            });
        });

        // Login redirect (sudah pakai route Laravel, tidak perlu JS redirect)
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                document.querySelector(targetId)?.scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Animasi Footer
        const footer = document.getElementById('main-footer');
        function checkFooterVisibility() {
            const footerRect = footer.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            if (footerRect.top <= windowHeight * 0.9) {
                footer.classList.add('visible');
            }
        }
        window.addEventListener('scroll', checkFooterVisibility);
        window.addEventListener('load', checkFooterVisibility);
    </script>
    @stack('scripts')
</body>
</html>

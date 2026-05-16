<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Admin - Dinas Lingkungan Hidup Kab. Nganjuk</title>
    <link rel="shortcut icon" href="{{ asset('assets/logo-dlh.png') }}" type="image/x-icon">
    
    {{-- FUNGSI: Memuat file CSS eksternal untuk halaman login --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    
    {{-- FUNGSI: Set background image via inline style karena pakai {{ asset() }} --}}
    <style>
        body {
            background: linear-gradient(rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.3)), url('{{ asset('assets/background-landing.png') }}') no-repeat center center/cover;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/logo-dlh.png') }}" alt="Logo DLH" class="header-logo">
        <div class="header-title">
            Login admin
            <span>Dinas Lingkungan Hidup Kab. Nganjuk</span>
        </div>
    </div>

    <div class="main-container">
        <div class="login-card">
            <div class="login-image-section">
                <img src="{{ asset('assets/background-landing.png') }}" alt="Gedung DLH Kab. Nganjuk">
            </div>
            
            <div class="login-form-section">
                <div class="form-logo">
                    <img src="{{ asset('assets/logo-resik.png') }}" alt="Logo DLH">
                </div>

                <form method="POST" action="{{ route('admin.login.post') }}" id="loginForm" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               autocomplete="off" 
                               placeholder="Masukkan email Anda" />
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Kata Sandi</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="Masukkan kata sandi">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility()">
                                <img src="{{ asset('assets/hide.png') }}" id="eyeIcon" alt="Toggle visibility">
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">Masuk</button>
                    
                    <div id="alertBox" class="alert"></div>
                </form>
            </div>
        </div>
    </div>

    <div id="popup" class="popup-overlay">
        <div class="popup-content">
            <h3 id="popup-title">Judul Popup</h3>
            <p id="popup-message">Pesan popup</p>
            <button class="popup-btn" onclick="closePopup()">Tutup</button>
        </div>
    </div>

    {{-- 🔗 BRIDGE: Pass dynamic data ke file JS eksternal --}}
    <script>
        window.LoginConfig = {
            iconShow: "{{ asset('assets/show1.png') }}",
            iconHide: "{{ asset('assets/hide1.png') }}",
            @if ($errors->any())
            popupTitle: "Gagal Login!",
            popupMessage: "{{ addslashes($errors->first()) }}",
            @endif
            @if(session('error'))
            popupTitle: "Error!",
            popupMessage: "{{ addslashes(session('error')) }}",
            @endif
        };
    </script>

    {{-- FUNGSI: Memuat file JS eksternal untuk interaksi login --}}
    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Admin - Dinas Lingkungan Hidup Kab. Nganjuk</title>
    <link rel="shortcut icon" href="{{ asset('assets/logo-dlh.png') }}" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        body { 
            background: linear-gradient(rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.3)), url('{{ asset('assets/background-landing.png') }}') no-repeat center center/cover;
            min-height: 100vh; 
            display: flex; 
            flex-direction: column;
        }
        
        .header {
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%;
            background: rgba(255,255,255,0.1); 
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 10px 30px;
            display: flex; 
            align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            z-index: 10;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .header-logo { 
            width: 40px; 
            height: 40px; 
            margin-right: 12px; 
            border-radius: 50%; 
            object-fit: cover; 
        }
        
        .header-title { 
            font-size: 1.1em; 
            font-weight: 600; 
            color: #1a5f1a;
        }
        
        .header-title span { 
            font-size: 0.85em; 
            font-weight: 400; 
            color: #555;
            display: block;
        }
        
        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 100px 20px 40px;
        }
        
        .login-card {
            width: 100%; 
            max-width: 850px; /* Diperkecil dari 1000px */
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px; 
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            overflow: hidden; 
            display: flex;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .login-image-section {
            flex: 1;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 0;
            position: relative;
            min-height: 450px; /* Diperkecil dari 550px */
            overflow: hidden;
        }
        
        .login-image-section img {
            width: 100%; 
            height: 100%; 
            object-fit: cover;
            object-position: 30% center;
        }
        
        .login-form-section { 
            flex: 0.85; /* Diperkecil dari 0.9 */
            padding: 40px 35px; /* Diperkecil dari 60px 50px */
            display: flex; 
            flex-direction: column; 
            justify-content: center;
            background: rgba(255, 255, 255, 0.7);
        }
        
        .form-logo {
            text-align: center;
            margin-bottom: 25px; /* Diperkecil dari 30px */
        }
        
        .form-logo img {
            width: 80px; /* Diperkecil dari 100px */
            height: 80px;
            object-fit: contain;
        }
        
        .form-group { 
            margin-bottom: 20px; /* Diperkecil dari 25px */
        }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 500; 
            color: #333; 
            font-size: 14px; 
        }
        
        input[type="email"], 
        input[type="password"] {
            width: 100%; 
            padding: 11px 14px; /* Sedikit diperkecil */
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px; 
            font-size: 14px; /* Diperkecil dari 15px */
            transition: all 0.3s ease;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.9);
        }
        
        input:focus { 
            outline: none; 
            border-color: #2e8b57; 
            box-shadow: 0 0 0 3px rgba(46,139,87,0.2); 
            background: rgba(255, 255, 255, 1);
        }
        
        .btn-login {
            background: #1a5f1a; 
            color: white; 
            border: none; 
            width: 100%;
            padding: 12px; /* Diperkecil dari 14px */
            border-radius: 8px; 
            font-size: 15px; /* Diperkecil dari 16px */
            font-weight: 600;
            cursor: pointer; 
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover { 
            background: #0f4d0f; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26,95,26,0.4);
        }
        
        .alert {
            padding: 12px; 
            border-radius: 8px; 
            margin-top: 15px;
            font-weight: 500; 
            background: rgba(255, 243, 205, 0.9); 
            color: #856404;
            border: 1px solid rgba(255, 234, 167, 0.9);
            display: none;
        }
        
        .alert.show {
            display: block;
            animation: fadeInUp 0.5s ease;
        }
        
        .popup-overlay {
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%;
            background-color: rgba(0,0,0,0.5); 
            display: flex;
            justify-content: center; 
            align-items: center; 
            z-index: 1000; 
            display: none;
        }
        
        .popup-overlay.show {
            display: flex;
        }
        
        .popup-content {
            background: white; 
            padding: 30px; 
            border-radius: 10px;
            width: 350px; 
            text-align: center; 
            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
            animation: popIn 0.3s ease;
        }
        
        .popup-content h3 { 
            margin: 0 0 15px 0; 
            font-size: 20px; 
            color: #333;
        }
        
        .popup-content p { 
            margin: 0 0 20px 0; 
            color: #666; 
        }
        
        .popup-btn {
            padding: 10px 25px; 
            background: #1a5f1a;
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer;
            font-weight: 600;
        }
        
        .popup-btn:hover { 
            background: #0f4d0f; 
        }

        .password-wrapper {
    position: relative;
    width: 100%;
    display: block; /* Pastikan wrapper tidak collapse */
}

.password-wrapper input {
    width: 100% !important;
    padding: 12px 45px 12px 15px !important; /* Kanan dikasih ruang buat icon */
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 8px;
    font-size: 14px;
    background: rgba(255, 255, 255, 0.9);
    box-sizing: border-box;
    transition: all 0.3s ease;
}

.password-wrapper input:focus {
    outline: none;
    border-color: #2e8b57;
    box-shadow: 0 0 0 3px rgba(46,139,87,0.2);
}
        .password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.password-toggle img {
    width: 20px;
    height: 20px;
    object-fit: contain;
   pointer-events: none;
}

.password-toggle img:hover {
    opacity: 1;
}

/* Untuk input yang sedang focus */
input:focus + .password-toggle img,
input:focus ~ .password-toggle img {
    opacity: 0.8;
}
        
        @keyframes popIn { 
            from { transform: scale(0.8); opacity: 0; } 
            to { transform: scale(1); opacity: 1; } 
        }
        
        @keyframes fadeInUp { 
            from { opacity: 0; transform: translateY(10px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        
        /* Responsive untuk laptop dan tablet */
        @media (max-width: 1200px) {
            .login-card {
                max-width: 750px; /* Lebih kecil untuk laptop 1366px */
            }
            .login-image-section {
                min-height: 400px;
            }
        }
        
        @media (max-width: 992px) {
            .login-card {
                max-width: 650px; /* Untuk tablet landscape */
            }
            .login-image-section {
                min-height: 350px;
            }
            .login-form-section {
                padding: 35px 30px;
            }
        }
        
        @media (max-width: 768px) {
            .login-card { 
                flex-direction: column; 
                max-width: 450px; /* Untuk tablet portrait */
            }
            .login-image-section { 
                min-height: 250px;
            }
            .login-form-section { 
                padding: 30px 25px; 
            }
            .header {
                padding: 8px 15px;
            }
            .header-title {
                font-size: 0.9em;
            }
        }
        
        @media (max-width: 480px) {
            .login-card {
                max-width: 95%; /* Untuk HP */
                margin: 0 10px;
            }
            .login-image-section {
                min-height: 200px;
            }
            .form-logo img {
                width: 70px;
                height: 70px;
            }
            .login-form-section {
                padding: 25px 20px;
            }
            input[type="email"],
            input[type="password"] {
                padding: 10px 12px;
                font-size: 13px;
            }
            .btn-login {
                padding: 11px;
                font-size: 14px;
            }
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

    <script>
        function showPopup(title, message) {
            document.getElementById('popup-title').textContent = title;
            document.getElementById('popup-message').textContent = message;
            document.getElementById('popup').classList.add('show');
        }

        function closePopup() {
            document.getElementById('popup').classList.remove('show');
        }

        function showAlert(message) {
            const alertBox = document.getElementById('alertBox');
            alertBox.textContent = message;
            alertBox.classList.add('show');
            setTimeout(() => {
                alertBox.classList.remove('show');
            }, 5000);
        }

        function togglePasswordVisibility() {
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (password.type === "password") {
        password.type = "text";
        eyeIcon.src = "{{ asset('assets/show1.png') }}";
        eyeIcon.alt = "Sembunyikan sandi";
    } else {
        password.type = "password";
        eyeIcon.src = "{{ asset('assets/hide1.png') }}";
        eyeIcon.alt = "Tampilkan sandi";
    }
}

        // Validasi & submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!email) {
                showAlert('Email wajib diisi.');
                return;
            }
            if (!password) {
                showAlert('Kata sandi wajib diisi.');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showAlert('Format email tidak valid.');
                return;
            }

            this.submit();
        });

        // Tutup popup saat klik di luar
        document.getElementById('popup').addEventListener('click', function(e) {
            if (e.target === this) {
                closePopup();
            }
        });

        // Tampilkan error Laravel
        @if ($errors->any())
            showPopup('Gagal Login!', '{{ addslashes($errors->first()) }}');
        @endif
        
        @if(session('error'))
            showPopup('Error!', '{{ addslashes(session('error')) }}');
        @endif
    </script>
</body>
</html>
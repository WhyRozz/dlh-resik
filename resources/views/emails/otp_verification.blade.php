<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        .app-name {
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            color: #1B5E20;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
        .message {
            color: #555555;
            font-size: 14px;
            line-height: 1.6;
            text-align: center;
            margin-bottom: 30px;
        }
        .otp-box {
            background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);
            border: 3px dashed #4CAF50;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            color: #2E7D32;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .otp-code {
            color: #1B5E20;
            font-size: 48px;
            font-weight: 900;
            letter-spacing: 8px;
            margin: 10px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .validity {
            color: #666666;
            font-size: 13px;
            margin-top: 15px;
        }
        .warning-box {
            background-color: #FFF3CD;
            border-left: 4px solid #FFC107;
            padding: 15px;
            margin: 25px 0;
            border-radius: 8px;
        }
        .warning-text {
            color: #856404;
            font-size: 13px;
            line-height: 1.5;
        }
        .footer {
            background-color: #F5F5F5;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #E0E0E0;
        }
        .footer-text {
            color: #757575;
            font-size: 12px;
            line-height: 1.5;
            margin: 5px 0;
        }
        .social-links {
            margin-top: 15px;
        }
        .social-icon {
            display: inline-block;
            width: 32px;
            height: 32px;
            margin: 0 8px;
            background-color: #4CAF50;
            border-radius: 50%;
            line-height: 32px;
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header dengan Logo -->
        <div class="header">
            {{-- Ganti dengan URL logo RESIK kamu --}}
            <img src="{{ asset('images/logo-resik.png') }}" 
                 alt="RESIK Logo" 
                 class="logo"
                 onerror="this.src='https://via.placeholder.com/80x80/4CAF50/FFFFFF?text=R'">
            <h1 class="app-name">RESIK App</h1>
        </div>

        <!-- Konten Utama -->
        <div class="content">
            <div class="greeting">
                Halo! 👋
            </div>
            
            <div class="message">
                Anda menerima email ini karena ada permintaan untuk mereset password 
                akun RESIK App Anda. Gunakan kode verifikasi di bawah ini untuk melanjutkan.
            </div>

            <!-- OTP Box dengan Angka Besar & Tebal -->
            <div class="otp-box">
                <div class="otp-label">Kode Verifikasi Anda</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="validity">
                    ⏱️ Berlaku selama {{ $expiresIn }} menit
                </div>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <div class="warning-text">
                    <strong>⚠️ Penting:</strong><br>
                    Jangan bagikan kode ini kepada siapa pun. 
                    Jika Anda tidak meminta reset password, abaikan email ini atau hubungi tim support kami.
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                <strong>Tim RESIK App</strong><br>
                Sistem Pengelolaan Sampah yang Modern dan Efisien
            </div>
            <div class="footer-text">
                📧 simpelsi2025@gmail.com<br>
                © {{ date('Y') }} RESIK App. All rights reserved.
            </div>
            <div class="social-links">
                <a href="#" class="social-icon">f</a>
                <a href="#" class="social-icon">in</a>
                <a href="#" class="social-icon">ig</a>
            </div>
        </div>
    </div>
</body>
</html>
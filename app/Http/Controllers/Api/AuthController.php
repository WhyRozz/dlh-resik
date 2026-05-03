<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masyarakat;
use App\Models\Pns;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ✅ REGISTER
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:masyarakat,email|unique:pns,email',
            'password' => 'required|min:6',
            'no_telepon' => 'required|string|max:15',
            'pekerjaan' => 'required|in:Masyarakat Umum,ASN / PNS',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'id_dinas' => 'required_if:pekerjaan,ASN / PNS|nullable|exists:dinas,id_dinas',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Generate barcode ID (15 karakter alphanumeric uppercase)
        $barcode_id = 'RK' . strtoupper(Str::random(13));

        // Hash password
        $hashedPassword = Hash::make($request->password);

        if (
            strpos(strtolower($request->pekerjaan), 'masyarakat') !== false ||
            strpos(strtolower($request->pekerjaan), 'umum') !== false
        ) {

            // Register sebagai Masyarakat
            $user = Masyarakat::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => $hashedPassword,
                'no_telepon' => $request->no_telepon,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'barcode_id' => $barcode_id,
                'saldo' => 0.00,
            ]);

            return response()->json([
                'status' => 'success',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'data' => [
                    'id_masyarakat' => $user->id_masyarakat,
                    'barcode_id' => $user->barcode_id,
                    'tipe' => 'masyarakat'
                ],
                'message' => 'Registrasi masyarakat berhasil'
            ], 201);
        } elseif (
            strpos(strtolower($request->pekerjaan), 'asn') !== false ||
            strpos(strtolower($request->pekerjaan), 'pns') !== false
        ) {

            // Generate kode anggota PNS
            $count = Pns::count() + 1;
            $kode_anggota = 'PNS-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            // Register sebagai PNS
            $user = Pns::create([
                'kode_anggota' => $kode_anggota,
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => $hashedPassword,
                'no_telepon' => $request->no_telepon,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'id_dinas' => $request->id_dinas,
                'barcode_id' => $barcode_id,
                'saldo' => 0.00,
            ]);

            return response()->json([
                'status' => 'success',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'data' => [
                    'id_pns' => $user->id_pns,
                    'kode_anggota' => $user->kode_anggota,
                    'barcode_id' => $user->barcode_id,
                    'tipe' => 'pns'
                ],
                'message' => 'Registrasi PNS berhasil'
            ], 201);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Tipe pekerjaan tidak valid'
        ], 422);
    }

    // ✅ LOGIN
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $email = trim($request->email);
        $password = $request->password;

        // 1. Cek tabel Masyarakat
        $user = Masyarakat::where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'success',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'data' => [
                    'tipe' => 'masyarakat',
                    'user' => $user
                ],
                'message' => 'Login berhasil'
            ]);
        }

        // 2. Cek tabel PNS (dengan relasi dinas)
        $user = Pns::with('dinas')->where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'success',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'data' => [
                    'tipe' => 'pns',
                    'user' => $user
                ],
                'message' => 'Login berhasil'
            ]);
        }

        // 3. Cek tabel Petugas (username atau email)
        $user = Petugas::where('username', $email)
            ->orWhere('email', $email)
            ->first();
        if ($user && Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'success',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'data' => [
                    'tipe' => 'petugas',
                    'user' => $user
                ],
                'message' => 'Login berhasil'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Email atau password salah'
        ], 401);
    }

    // ✅ FORGOT PASSWORD (Generate 4-digit OTP)
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $email = trim($request->email);
        $user_found = false;
        $user_type = '';

        // Cek di tabel masyarakat
        $user = Masyarakat::where('email', $email)->first();
        if ($user) {
            $user_found = true;
            $user_type = 'masyarakat';
        }

        // Cek di tabel pns
        if (!$user_found) {
            $user = Pns::where('email', $email)->first();
            if ($user) {
                $user_found = true;
                $user_type = 'pns';
            }
        }

        if ($user_found) {
            // ✅ Generate 4-digit OTP numeric (0000 - 9999)
            $otp = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $otp_expires = now()->addMinutes(10); // Valid 10 menit

            if ($user_type === 'masyarakat') {
                Masyarakat::where('email', $email)->update([
                    'otp' => $otp,
                    'otp_expires' => $otp_expires
                ]);
            } else {
                Pns::where('email', $email)->update([
                    'otp' => $otp,
                    'otp_expires' => $otp_expires
                ]);
            }

            // TODO: Nanti integrasi kirim email/SMS OTP
            return response()->json([
                'status' => 'success',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'message' => 'Kode verifikasi telah dikirim ke email Anda'
            ]);
        }

        // Keamanan: tetap return success agar tidak bisa enumerate email
        return response()->json([
            'status' => 'success',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'message' => 'Jika email terdaftar, kode verifikasi telah dikirim'
        ]);
    }
    // ✅ VERIFY OTP
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $email = trim($request->email);
        $otp = $request->otp;

        // Cek di tabel masyarakat
        $user = Masyarakat::where('email', $email)
            ->where('otp', $otp)
            ->where('otp_expires', '>', now())
            ->first();

        // Kalau tidak ada di masyarakat, cek di pns
        if (!$user) {
            $user = Pns::where('email', $email)
                ->where('otp', $otp)
                ->where('otp_expires', '>', now())
                ->first();
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode verifikasi salah atau sudah kadaluarsa'
            ], 422);
        }

        // Generate reset token untuk langkah selanjutnya
        $resetToken = bin2hex(random_bytes(32));
        $resetExpires = now()->addHour();

        // Simpan reset token
        if ($user instanceof Masyarakat) {
            Masyarakat::where('email', $email)->update([
                'reset_token' => $resetToken,
                'reset_token_expires' => $resetExpires,
            ]);
        } else {
            Pns::where('email', $email)->update([
                'reset_token' => $resetToken,
                'reset_token_expires' => $resetExpires,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'data' => [
                'token' => $resetToken,
                'email' => $email
            ],
            'message' => 'Verifikasi berhasil'
        ]);
    }
    // ✅ RESET PASSWORD
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $email = trim($request->email);
        $token = $request->token;
        $password = $request->password;

        // Cek token di tabel masyarakat
        $user = Masyarakat::where('email', $email)
            ->where('reset_token', $token)
            ->where('reset_token_expires', '>', now())
            ->first();

        // Kalau tidak ada di masyarakat, cek di pns
        if (!$user) {
            $user = Pns::where('email', $email)
                ->where('reset_token', $token)
                ->where('reset_token_expires', '>', now())
                ->first();
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid atau sudah kadaluarsa'
            ], 422);
        }

        // Update password
        $hashedPassword = Hash::make($password);

        if ($user instanceof Masyarakat) {
            Masyarakat::where('email', $email)->update([
                'password' => $hashedPassword,
                'reset_token' => null,
                'reset_token_expires' => null,
                'otp' => null,
                'otp_expires' => null,
            ]);
        } else {
            Pns::where('email', $email)->update([
                'password' => $hashedPassword,
                'reset_token' => null,
                'reset_token_expires' => null,
                'otp' => null,
                'otp_expires' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'message' => 'Password berhasil direset'
        ]);
    }
}

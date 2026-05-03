<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Petugas; // ← PENTING: Import Model Petugas
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // ← TAMBAHKAN INI
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * Konstanta
     */
    const MAX_ADMIN_ACCOUNTS = 3;
    const DEFAULT_ADMIN_EMAIL = 'simpelsi2025@gmail.com';
    const OTP_EXPIRE_MINUTES = 5;

    /**
     * Tampilkan halaman kelola akun
     */
    public function index()
    {
        // 1. Ambil data Admin
        $admins = Admin::orderByRaw("
            CASE WHEN email = ? THEN 0 ELSE 1 END,
            id_admin ASC
        ", [self::DEFAULT_ADMIN_EMAIL])->get();

        $akunUtama = $admins->firstWhere('email', self::DEFAULT_ADMIN_EMAIL);
        $tambahan = $admins->reject(fn($a) => $a->email === self::DEFAULT_ADMIN_EMAIL)->values();

        // 2. Ambil data Petugas
        $petugas = \App\Models\Petugas::orderBy('created_at', 'desc')->get();

        // 3. Kirim ke view
        return view('admin.account.index', compact('admins', 'akunUtama', 'tambahan', 'petugas'));
    }

    /**
     * Simpan akun baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:admin,email',
            'password' => 'required|string|min:8|max:50|regex:/^[a-zA-Z0-9\s]+$/',
        ], [
            'password.regex' => 'Sandi tidak boleh mengandung karakter spesial. Hanya boleh huruf, angka, dan spasi.',
        ]);

        // Cek batas maksimal akun
        if (Admin::count() >= self::MAX_ADMIN_ACCOUNTS) {
            return back()->with('error', 'Jumlah akun admin sudah mencapai batas maksimal (3).');
        }

        // Hash & encrypt password
        $hashedPassword = Hash::make($validated['password']);
        $encryptedPassword = EncryptionService::encrypt($validated['password']);

        if (!$hashedPassword || !$encryptedPassword) {
            return back()->with('error', 'Gagal membuat hash/enkripsi password.');
        }

        Admin::create([
            'email' => $validated['email'],
            'password' => $hashedPassword,
            'password_encrypted' => $encryptedPassword,
        ]);

        return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    /**
     * Update akun existing
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:admin,email,' . $id . ',id_admin',
            'password' => 'nullable|string|min:8|max:50|regex:/^[a-zA-Z0-9\s]+$/',
        ], [
            'password.regex' => 'Sandi tidak boleh mengandung karakter spesial. Hanya boleh huruf, angka, dan spasi.',
        ]);

        $updateData = ['email' => $validated['email']];

        // Update password hanya jika diisi & bukan placeholder
        if (!empty($validated['password']) && $validated['password'] !== '••••••••') {
            $hashedPassword = Hash::make($validated['password']);
            $encryptedPassword = EncryptionService::encrypt($validated['password']);

            if (!$hashedPassword || !$encryptedPassword) {
                return back()->with('error', 'Gagal membuat hash/enkripsi password.');
            }

            $updateData['password'] = $hashedPassword;
            $updateData['password_encrypted'] = $encryptedPassword;
        }

        $admin->update($updateData);

        return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Hapus akun (hanya untuk akun tambahan, bukan default)
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);

        // Jangan hapus akun default
        if ($admin->email === self::DEFAULT_ADMIN_EMAIL) {
            return back()->with('error', 'Akun utama tidak dapat dihapus.');
        }

        $admin->delete();

        return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil dihapus.');
    }

    /**
     * Request OTP untuk verifikasi aksi sensitif
     */
    public function requestOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:admin,email',
        ]);

        // Generate OTP 4 digit
        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(self::OTP_EXPIRE_MINUTES);

        // Simpan di cache
        Cache::put('admin_otp_' . $validated['email'], $otp, $expiresAt);

        // Update DB juga
        Admin::where('email', $validated['email'])->update([
            'otp' => $otp,
            'otp_expires' => $expiresAt,
        ]);

        // Kirim email
        Mail::raw("Kode OTP Admin SIMPELSI Anda: {$otp}\nBerlaku selama " . self::OTP_EXPIRE_MINUTES . " menit.", function ($message) use ($validated) {
            $message->to($validated['email'])->subject('Kode OTP Admin - SIMPELSI');
        });

        return response()->json([
            'status' => app()->environment('local') ? 'success_dev' : 'success',
            'message' => 'Kode OTP telah dikirim ke email Anda.',
            'otp' => app()->environment('local') ? $otp : null,
        ]);
    }

    /**
     * Verifikasi OTP
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:4',
        ]);

        $cachedOtp = Cache::get('admin_otp_' . $validated['email']);
        $admin = Admin::where('email', $validated['email'])->first();

        // Cek OTP dari cache atau DB
        $isValid = ($cachedOtp && $cachedOtp === $validated['otp']) ||
                   ($admin && $admin->otp === $validated['otp'] && $admin->otp_expires > now());

        if (!$isValid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa.'
            ], 400);
        }

        // Hapus OTP setelah berhasil
        Cache::forget('admin_otp_' . $validated['email']);
        if ($admin) {
            $admin->update(['otp' => null, 'otp_expires' => null]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP berhasil diverifikasi.'
        ]);
    }

    /**
     * AJAX: Get password placeholder
     */
    public function getPasswordPlaceholder(Request $request)
    {
        $validated = $request->validate(['id_admin' => 'required|integer|exists:admin,id_admin']);

        $admin = Admin::find($validated['id_admin']);

        return response()->json([
            'status' => 'success',
            'password' => '••••••••'
        ]);
    }

    /**
     * AJAX: Get password raw (decrypted)
     */
    public function getPasswordRaw(Request $request)
    {
        $validated = $request->validate(['id_admin' => 'required|integer|exists:admin,id_admin']);

        $admin = Admin::find($validated['id_admin']);

        if (!$admin || empty($admin->password_encrypted)) {
            return response()->json([
                'status' => 'success',
                'password' => '••••••••'
            ]);
        }

        $decrypted = EncryptionService::decrypt($admin->password_encrypted);

        return response()->json([
            'status' => 'success',
            'password' => $decrypted ?: '••••••••'
        ]);
    }

    /**
     * Get admin data (JSON) - untuk edit
     */
    public function show($id)
    {
        $admin = Admin::findOrFail($id);
        return response()->json([
            'email' => $admin->email
        ]);
    }
}
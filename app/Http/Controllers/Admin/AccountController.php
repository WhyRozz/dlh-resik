<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Petugas;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * Konstanta
     */
    const MAX_ADMIN_ACCOUNTS = 3;
    // ✅ DEFAULT_ADMIN_EMAIL DIHAPUS - sekarang ambil dari database
    const OTP_EXPIRE_MINUTES = 5;

    /**
     * Tampilkan halaman kelola akun
     */
    public function index()
    {
        // ✅ Ambil admin utama berdasarkan id_admin terkecil (bukan hardcoded email)
        $adminUtama = Admin::orderBy('id_admin', 'asc')->first();
        
        // ✅ Ambil semua admin
        $admins = Admin::orderBy('id_admin', 'asc')->get();
        
        // ✅ Pisahkan admin utama dan tambahan
        $akunUtama = $adminUtama;
        $tambahan = $admins->reject(fn($a) => $a->id_admin === ($adminUtama?->id_admin))->values();

        // ✅ Ambil data Petugas
        $petugas = \App\Models\Petugas::orderBy('created_at', 'desc')->get();

        // ✅ Kirim ke view
        return view('admin.account.index', compact('admins', 'akunUtama', 'tambahan', 'petugas'));
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
     * ✅ BARU: Get semua email admin dari database (untuk modal OTP)
     */
    public function getAdminEmails()
    {
        $admins = Admin::select('id_admin', 'email')->orderBy('id_admin', 'asc')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'admins' => $admins
            ]
        ]);
    }

    /**
     * ✅ BARU: Proses kirim OTP ke email admin (via admin_id)
     */
    public function processSendOtp(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|integer|exists:admin,id_admin',
        ]);

        $admin = Admin::find($validated['admin_id']);
        
        if (!$admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }
        
        // Generate OTP 6 digit
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(self::OTP_EXPIRE_MINUTES);

        // Simpan di cache
        Cache::put('admin_otp_' . $admin->email, $otp, $expiresAt);

        // Update DB juga
        Admin::where('id_admin', $admin->id_admin)->update([
            'otp' => $otp,
            'otp_expires' => $expiresAt,
        ]);

        // Kirim email
        try {
            Mail::raw("
                Kode OTP Admin RESIK: {$otp}
                
                Berlaku selama " . self::OTP_EXPIRE_MINUTES . " menit.
                
                Jangan bagikan kode ini kepada siapa pun.
                
                Jika Anda tidak meminta kode ini, abaikan email ini.
            ", function ($message) use ($admin) {
                $message->to($admin->email)
                        ->subject('Kode OTP Admin - RESIK');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'OTP berhasil dikirim ke ' . $admin->email
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim OTP: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim email'
            ], 500);
        }
    }

    /**
     * Request OTP untuk verifikasi aksi sensitif
     * ✅ DIUPDATE: Sekarang support via email (untuk backward compatibility)
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
        try {
            Mail::raw("Kode OTP Admin RESIK Anda: {$otp}\nBerlaku selama " . self::OTP_EXPIRE_MINUTES . " menit.", function ($message) use ($validated) {
                $message->to($validated['email'])->subject('Kode OTP Admin - RESIK');
            });

            return response()->json([
                'status' => app()->environment('local') ? 'success_dev' : 'success',
                'message' => 'Kode OTP telah dikirim ke email Anda.',
                'otp' => app()->environment('local') ? $otp : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim OTP: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim email'
            ], 500);
        }
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
    
    
/**
 * Hapus akun admin
 */
public function destroy($id)
{
    $admin = Admin::findOrFail($id);
    
    // ⚠️ Opsional: Cegah hapus akun utama (admin dengan id_admin terkecil)
    $primaryAdminId = Admin::min('id_admin');
    if ($admin->id_admin === $primaryAdminId) {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun utama tidak dapat dihapus.'
            ], 403);
        }
        return back()->with('error', 'Akun utama tidak dapat dihapus.');
    }
    
    $admin->delete();
    
    if (request()->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus.'
        ]);
    }
    
    return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil dihapus.');
}
}
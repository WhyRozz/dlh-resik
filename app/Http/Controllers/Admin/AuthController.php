<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Tampilkan form login
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * Proses login admin
     */
    public function login(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:50',
            ],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Sandi wajib diisi.',
            'password.min' => 'Sandi minimal 8 karakter.',
            'password.max' => 'Sandi maksimal 50 karakter.',
            'password.regex' => 'Sandi tidak boleh mengandung karakter spesial. Hanya boleh huruf, angka, dan spasi.',
        ]);

        // Cari admin berdasarkan email
        $admin = Admin::where('email', $validated['email'])->first();

        if (!$admin) {
            return back()
                ->withErrors(['email' => 'Email atau sandi salah. Silakan coba lagi.'])
                ->withInput($request->only('email'));
        }

        // Verifikasi password
        if (!Hash::check($validated['password'], $admin->password)) {
            return back()
                ->withErrors(['email' => 'Email atau sandi salah. Silakan coba lagi.'])
                ->withInput($request->only('email'));
        }

        // Login berhasil
        Auth::guard('admin')->login($admin);

        // Redirect berdasarkan role
        if ($admin->isSubAdminDesa()) {
            return redirect()->route('admin.sub-admin.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

      public function saveFcmToken(Request $request)
    {
        \Log::info('=== WEB ADMIN SAVE FCM TOKEN ===', $request->all());

        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin belum login.'], 401);
        }

        $request->validate([
            'token'       => 'required|string',
            'device_name' => 'nullable|string|max:255',
            'device_id'   => 'nullable|string|max:100',
        ]);

        $token      = $request->token;
        $deviceName = $request->device_name ?? 'Unknown Device';
        // ✅ Kunci device: pakai device_id (kalau ada), fallback ke device_name
        $deviceKey  = $request->device_id ?: ($request->device_name ?: 'default');

        try {
            // ✅ 1 device = 1 baris. Kunci unik = (id_admin + device_id)
            //    Token berganti di device yang sama → UPDATE baris yang sama, bukan nambah.
            DB::table('admin_fcm_tokens')->updateOrInsert(
                ['id_admin' => $admin->id_admin, 'device_id' => $deviceKey],
                [
                    'fcm_token'   => $token,
                    'device_name' => $deviceName,
                    'last_active' => now(),
                    'updated_at'  => now(),
                ]
            );

            // isi created_at kalau baris baru
            DB::table('admin_fcm_tokens')
                ->where('id_admin', $admin->id_admin)
                ->where('device_id', $deviceKey)
                ->whereNull('created_at')
                ->update(['created_at' => now()]);

            \Log::info("✅ Token tersimpan | admin {$admin->id_admin} | device {$deviceKey}");

            return response()->json(['success' => true, 'message' => 'Token tersimpan (1 device = 1 token).']);
        } catch (\Exception $e) {
            \Log::error('❌ ERROR SIMPAN TOKEN: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

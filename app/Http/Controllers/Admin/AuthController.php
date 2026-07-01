<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $request->validate([
            'token' => 'required|string',
        ]);

        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin belum login.'
            ], 401);
        }

        $admin->fcm_token = $request->token;
        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'FCM Token berhasil disimpan.'
        ]);
    }
}

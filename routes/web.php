<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// ✅ Controllers umum
use App\Http\Controllers\LandingController;

// ✅ Admin Controllers
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\ArtikelController;
use App\Http\Controllers\Admin\TpsController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\PetugasController;
use App\Http\Controllers\Admin\DataPenggunaController;

// ✅ BankSampah Controllers
use App\Http\Controllers\BankSampah\PenarikanController;
use App\Http\Controllers\BankSampah\PenjemputanController;
use App\Http\Controllers\BankSampah\SetorController; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page (Public)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Admin Routes - Group Utama
Route::prefix('admin')->name('admin.')->group(function () {

    // ── Guest routes (belum login) ──
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    });

    // ── Protected routes (sudah login) ──
    Route::middleware('auth:admin')->group(function () {

        // Logout
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ── Data Pengguna ──
        Route::prefix('data-pengguna')->name('data-pengguna.')->group(function () {
            Route::get('/', [DataPenggunaController::class, 'index'])->name('index');
            Route::get('/export', [DataPenggunaController::class, 'export'])->name('export');

            // API untuk detail user (modal)
            Route::get('/api/{type}/{id}', [DataPenggunaController::class, 'show'])
                ->where(['type' => 'masyarakat|pns'])
                ->name('api.show');
        });

        // ── Kelola Laporan ──
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', [LaporanController::class, 'index'])->name('index');
            Route::post('/update-status', [LaporanController::class, 'updateStatus'])->name('update-status');
        });

        // ── Kelola Artikel ──
        Route::prefix('artikel')->name('artikel.')->group(function () {
            Route::get('/', [ArtikelController::class, 'index'])->name('index');
            Route::get('/create', [ArtikelController::class, 'create'])->name('create');
            Route::post('/', [ArtikelController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ArtikelController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ArtikelController::class, 'update'])->name('update');
            Route::delete('/{id}', [ArtikelController::class, 'destroy'])->name('destroy');
        });

        // ── Kelola TPS ──
        Route::prefix('tps')->name('tps.')->group(function () {
            Route::get('/', [TpsController::class, 'index'])->name('index');
            Route::get('/create', [TpsController::class, 'create'])->name('create');
            Route::post('/', [TpsController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [TpsController::class, 'edit'])->name('edit');
            Route::put('/{id}', [TpsController::class, 'update'])->name('update');
            Route::delete('/{id}', [TpsController::class, 'destroy'])->name('destroy');
        });

        // ── Kelola Akun Admin (dengan OTP) ──
        Route::prefix('akun')->name('akun.')->group(function () {
            Route::get('/', [AccountController::class, 'index'])->name('index');
            Route::post('/', [AccountController::class, 'store'])->name('store');
            Route::get('/{id}', [AccountController::class, 'show'])->name('show');
            Route::put('/{id}', [AccountController::class, 'update'])->name('update');
            Route::delete('/{id}', [AccountController::class, 'destroy'])->name('destroy');

            // OTP
            Route::post('/request-otp', [AccountController::class, 'requestOtp'])->name('request-otp');
            Route::post('/verify-otp', [AccountController::class, 'verifyOtp'])->name('verify-otp');

            // AJAX Password
            Route::post('/ajax/get-password', [AccountController::class, 'getPasswordPlaceholder'])->name('ajax.get-password');
            Route::post('/ajax/get-password-raw', [AccountController::class, 'getPasswordRaw'])->name('ajax.get-password-raw');
        });

        // ── Kelola Petugas (tanpa OTP) ──
        Route::prefix('petugas')->name('petugas.')->group(function () {
            Route::post('/', [PetugasController::class, 'store'])->name('store');
            Route::get('/{id}', [PetugasController::class, 'show'])->name('show');
            Route::put('/{id}', [PetugasController::class, 'update'])->name('update');
            Route::delete('/{id}', [PetugasController::class, 'destroy'])->name('destroy');
        });

        // ========================================
        // ✅ BANK SAMPAH ROUTES - DIPERBAIKI
        // ========================================
        // ⚠️ Cukup prefix('bank-sampah') dan name('bank-sampah.')
        // Karena sudah di dalam prefix('admin') dan name('admin.')
        Route::prefix('bank-sampah')->name('bank-sampah.')->group(function () {
            
            // Data Setor (placeholder)
            Route::get('/setor', function() {
                return redirect()->back()->with('info', 'Fitur Data Setor belum tersedia.');
            })->name('setor');
            
            // ✅ Data Penarikan - INI YANG DICARI SIDEBAR
            Route::get('/penarikan', [PenarikanController::class, 'index'])->name('penarikan');
            Route::get('/penarikan/{id}', [PenarikanController::class, 'show'])->name('penarikan.show');
            Route::put('/penarikan/{id}/status', [PenarikanController::class, 'updateStatus'])->name('penarikan.update-status');
            Route::delete('/penarikan/{id}', [PenarikanController::class, 'destroy'])->name('penarikan.destroy');
            
            // Jenis & Harga Sampah (placeholder)
            Route::get('/jenis-sampah', function() {
                return redirect()->back()->with('info', 'Fitur Jenis Sampah belum tersedia.');
            })->name('jenis-sampah.index');
            
            // Penjemputan (placeholder)
            Route::get('/penjemputan', function() {
                return redirect()->back()->with('info', 'Fitur Penjemputan belum tersedia.');
            })->name('penjemputan.index');
            
        }); // ← Tutup group bank-sampah

    }); // ← Tutup group auth:admin

}); // ← Tutup group prefix admin

// ✅ API Route untuk detail user (di luar group admin agar URL konsisten)
Route::middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('/api/users/{id}', function ($id) {
        $user = DB::table('masyarakat')
            ->where('id_masyarakat', $id)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'id_masyarakat' => $user->id_masyarakat,
            'nama' => $user->nama,
            'email' => $user->email,
            'jenis_kelamin' => $user->jenis_kelamin,
            'no_telp' => $user->no_telp,
            'pekerjaan' => $user->pekerjaan,
            'alamat' => $user->alamat,
            'saldo_bank_sampah' => $user->saldo_bank_sampah ?? 0,
            'qr_code_path' => $user->qr_code_path,
            'created_at' => $user->created_at,
        ]);
    })->name('admin.api.users.show');
});
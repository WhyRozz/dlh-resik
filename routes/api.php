<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DinasController;
use App\Http\Controllers\Api\ArtikelController;
use App\Http\Controllers\Api\PenarikanController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TpsController;
use App\Http\Controllers\Api\JenisSampahController;
use App\Http\Controllers\Api\SetorController;
use App\Http\Controllers\Api\PenjemputanController;

// ==================== 🔓 PUBLIC ROUTES (Tanpa Login) ====================

// Auth - Register & Login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Auth - Forgot Password Flow
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Profile routes
Route::put('/profile', [ProfileController::class, 'update']);        // Untuk update tanpa foto
Route::post('/profile', [ProfileController::class, 'updateWithPhoto']); // Untuk update dengan foto

// Data Publik
Route::get('/dinas', [DinasController::class, 'index']);
Route::get('/artikel', [ArtikelController::class, 'index']);

Route::post('/profile', [ProfileController::class, 'update']);

Route::post('/cari-pengguna', [SetorController::class, 'cariPengguna']);

Route::post('/cari-pengguna', [SetorController::class, 'cariPengguna']);
Route::post('/transaksi-setor', [SetorController::class, 'store']);
Route::get('/riwayat-setor', [SetorController::class, 'riwayatSetor']);
// Penjemputan
Route::post('/penjemputan', [PenjemputanController::class, 'store']);
Route::get('/riwayat-penjemputan/{admin_id}', [PenjemputanController::class, 'index']);

// List jenis sampah
Route::get('/jenis-sampah', [JenisSampahController::class, 'index']);


// ==================== 🔐 PROTECTED ROUTES (Butuh Login - Sanctum) ====================

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/total-setoran', [AuthController::class, 'totalSetoran']); // GET: Total setoran
});

// 📝 Laporan Sampah Ilegal
Route::post('/laporan', [LaporanController::class, 'store']);
Route::get('/laporan', [LaporanController::class, 'index']);

Route::get('/get-saldo', [AuthController::class, 'getSaldo']);

// 💰 Penarikan Dana (Bank Sampah)
Route::post('/penarikan', [PenarikanController::class, 'store']);
Route::get('/penarikan', [PenarikanController::class, 'index']);

// Route::get('/riwayat-setor', [SetorController::class, 'index']);

Route::get('/tps', [TpsController::class, 'index']);           // List semua TPS
Route::get('/tps/{id}', [TpsController::class, 'show']);       // Detail TPS by ID

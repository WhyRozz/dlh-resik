<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DinasController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ArtikelController;
use App\Http\Controllers\Api\PenarikanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✅ AUTH ROUTES (dari AuthController)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Forgot Password & Verify OTP
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ✅ DINAS ROUTE (dari DinasController)
Route::get('/dinas', [DinasController::class, 'index']);
Route::post('/profile/update', [AuthController::class, 'updateProfile']);
Route::put('/profile', [AuthController::class, 'updateProfile']);
Route::get('/get-saldo', [AuthController::class, 'getSaldo']);

// Route Artikel
Route::get('/artikel', [ArtikelController::class, 'index']);

// Route Penarikan
Route::post('/penarikan', [PenarikanController::class, 'store']);
Route::get('/penarikan', [PenarikanController::class, 'index']);

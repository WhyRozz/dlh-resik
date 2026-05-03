<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Jangan redirect jika request expects JSON (API)
        if ($request->expectsJson()) {
            return null;
        }

        // ✅ Redirect ke admin.login jika route-nya ada
        if (Route::has('admin.login')) {
            return route('admin.login');
        }

        // ✅ Fallback ke route 'login' jika ada (untuk user biasa)
        if (Route::has('login')) {
            return route('login');
        }

        // ✅ Fallback terakhir: redirect ke URL path langsung (lebih aman)
        return '/admin/login';
    }
}
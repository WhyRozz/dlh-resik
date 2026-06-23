<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Symfony\Component\HttpFoundation\Response;

class SubAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Super Admin bisa akses semua
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Sub Admin harus punya id_desa
        if ($admin->isSubAdminDesa() && !$admin->id_desa) {
            return redirect()->route('admin.dashboard')->with('error', 'Akun belum dikonfigurasi.');
        }

        return $next($request);
    }
}
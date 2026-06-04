<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NoCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Jangan cache untuk BinaryFileResponse (download file)
        if ($response instanceof BinaryFileResponse) {
            return $response;
        }
        
        // Tambahkan header no-cache untuk response lainnya
        return $response->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                       ->header('Pragma', 'no-cache')
                       ->header('Expires', '0');
    }
}
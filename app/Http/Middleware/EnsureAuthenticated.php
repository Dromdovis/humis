<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('authenticated')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Neprisijungta'], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Prašome prisijungti.');
        }

        return $next($request);
    }
}

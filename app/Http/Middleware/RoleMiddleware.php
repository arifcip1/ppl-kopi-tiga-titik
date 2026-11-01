<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request and enforce role-based access (FR-1.5).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Jika pengguna belum login (Guest) -> arahkan ke halaman login
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan masuk terlebih dahulu.',
                ], 401);
            }

            return redirect()->guest('/login')->with('error', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
        }

        $user = Auth::user();

        // 2. Jika role pengguna tidak sesuai dengan role yang diizinkan -> beri response 403 Forbidden
        if (!empty($roles) && !in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki hak akses untuk fitur ini.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses untuk fitur ini.');
        }

        return $next($request);
    }
}


<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Standarisasi error validasi format JSON sesuai Kontrak Bab 13 & BR-41
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data input yang dimasukkan tidak valid.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // Penanganan 404 Model Eloquent Tidak Ditemukan
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sumber daya yang diminta tidak ditemukan.',
                ], 404);
            }
            return response()->view('errors.404', [
                'title' => 'Data Tidak Ditemukan',
                'message' => 'Data produk atau pesanan yang Anda cari tidak tersedia.',
            ], 404);
        });

        // Penanganan 404 Rute / Halaman Tidak Ditemukan
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint API tidak ditemukan.',
                ], 404);
            }
            return response()->view('errors.404', [], 404);
        });

        // Penanganan 403 Akses Ditolak (Otorisasi Peran)
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.',
                ], 403);
            }
            return response()->view('errors.403', [
                'message' => $e->getMessage() ?: 'Akses ditolak. Halaman ini hanya untuk pengguna dengan hak akses tertentu.',
            ], 403);
        });

        // Penanganan 401 Autentikasi / Sesi Berakhir
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi autentikasi Anda telah berakhir. Silakan login kembali.',
                ], 401);
            }
            return redirect()->guest(route('login'))->with('error', 'Silakan login terlebih dahulu untuk melanjutkan.');
        });

        // Penanganan 419 CSRF Token Expired
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token CSRF sesi halaman telah kedaluwarsa. Silakan muat ulang halaman.',
                ], 419);
            }
            return response()->view('errors.419', [], 419);
        });
    })->create();

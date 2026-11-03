<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('Login Page');
    }

    /**
     * Proses autentikasi login (FR-1.4 & Rate Limiting).
     */
    public function login(Request $request)
    {
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        // Batasi percobaan login gagal (Rate Limiting / Throttling)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan masuk gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Validasi kecocokan role pilihan di form dengan role akun di database (Revisi QA Tester)
            if ($request->filled('role')) {
                $expectedRole = match ($request->input('role')) {
                    'owner', 'admin' => 'admin',
                    'kurir'          => 'kurir',
                    default          => 'pelanggan',
                };

                if ($user->role !== $expectedRole) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors([
                        'email' => 'Peran akun yang dipilih tidak sesuai dengan akun Anda.',
                    ])->onlyInput('email');
                }
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return $this->redirectBasedOnRole(Auth::user());
        }

        RateLimiter::hit($throttleKey, 60);

        // Pesan aman yang tidak membocorkan apakah email terdaftar (FR-1.4)
        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Tampilkan formulir registrasi pelanggan baru (FR-1.3).
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('Register Page');
    }

    /**
     * Proses registrasi akun pelanggan baru (FR-1.3).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'name.max'           => 'Nama lengkap maksimal 255 karakter.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Alamat email ini sudah terdaftar.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'role'              => 'pelanggan',
            'email_verified_at' => null, // Bug 3 QA: email_verified_at default null tanpa auto-timestamp
        ]);

        // Inisialisasi 2 produk bawaan di keranjang sesuai UI Cart Page (Titik Temu: 2 botol & Titik Hitam: 1 botol)
        $titikTemu = Product::where('nama', 'Titik Temu')->first();
        $titikHitam = Product::where('nama', 'Titik Hitam')->first();

        if ($titikTemu) {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $titikTemu->id,
                'jumlah' => 2,
            ]);
        }
        if ($titikHitam) {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $titikHitam->id,
                'jumlah' => 1,
            ]);
        }

        // Login otomatis setelah registrasi berhasil
        Auth::login($user);
        $request->session()->regenerate();

        // Bug 1 QA: Pastikan sesi keranjang diinisialisasi kosong untuk pelanggan baru
        $request->session()->forget(['cart', 'cart_items']);

        return redirect('/katalog')->with('success', 'Akun berhasil didaftarkan! Selamat datang di Kopi Tiga Titik.');
    }

    /**
     * Proses logout pengguna (FR-1.4).
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari akun.');
    }

    /**
     * Pengalihan halaman berdasarkan role pengguna (FR-1.4).
     */
    private function redirectBasedOnRole(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->intended('/owner/dashboard');
        } elseif ($user->role === 'kurir') {
            return redirect()->intended('/kurir/tugas-baru');
        }

        return redirect()->intended('/katalog');
    }
}

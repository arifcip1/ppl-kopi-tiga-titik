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
        $rawLoginInput = trim((string) $request->input('email', ''));
        $normalizedKey = filter_var($rawLoginInput, FILTER_VALIDATE_EMAIL) !== false
            ? Str::lower($rawLoginInput)
            : ($this->normalizePhone($rawLoginInput) ?: Str::lower($rawLoginInput));
        $throttleKey = Str::transliterate($normalizedKey . '|' . $request->ip());

        // Batasi percobaan login gagal (Rate Limiting / Throttling)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan masuk gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $trimmed = trim((string) $value);
                    if ($trimmed === '') {
                        $fail('Email atau nomor WhatsApp wajib diisi.');
                        return;
                    }

                    $isEmail = filter_var($trimmed, FILTER_VALIDATE_EMAIL) !== false;
                    $digits = preg_replace('/\D+/', '', $trimmed);
                    $isPhone = preg_match('/^\+?[\d\s\-()]{8,20}$/', $trimmed) === 1
                        && strlen($digits) >= 9
                        && strlen($digits) <= 15;

                    if (!$isEmail && !$isPhone) {
                        $fail('Format alamat email atau nomor WhatsApp tidak valid.');
                    }
                },
            ],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email atau nomor WhatsApp wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginIdentifier = trim($credentials['email']);
        $remember = $request->boolean('remember');
        $authenticated = false;

        if (filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL) !== false) {
            $authenticated = Auth::attempt([
                'email'    => $loginIdentifier,
                'password' => $credentials['password'],
            ], $remember);
        } else {
            $matchedUser = $this->findUserByPhone($loginIdentifier);
            if ($matchedUser !== null) {
                $authenticated = Auth::attempt([
                    'id'       => $matchedUser->id,
                    'password' => $credentials['password'],
                ], $remember);
            }
        }

        if ($authenticated) {
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
            'phone'    => [
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $trimmed = trim((string) $value);
                    if ($trimmed === '') {
                        return;
                    }
                    $digits = preg_replace('/\D+/', '', $trimmed);
                    if (strlen($digits) < 9 || strlen($digits) > 15) {
                        $fail('Format nomor WhatsApp tidak valid (minimal 9 digit angka).');
                    }
                },
            ],
            'telepon'  => [
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $trimmed = trim((string) $value);
                    if ($trimmed === '') {
                        return;
                    }
                    $digits = preg_replace('/\D+/', '', $trimmed);
                    if (strlen($digits) < 9 || strlen($digits) > 15) {
                        $fail('Format nomor WhatsApp tidak valid (minimal 9 digit angka).');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'name.max'           => 'Nama lengkap maksimal 255 karakter.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Alamat email ini sudah terdaftar.',
            'phone.max'          => 'Nomor WhatsApp maksimal 20 karakter.',
            'telepon.max'        => 'Nomor WhatsApp maksimal 20 karakter.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $rawPhone = trim((string) ($validated['phone'] ?? $validated['telepon'] ?? ''));
        $normalizedPhone = $rawPhone !== '' ? ($this->normalizePhone($rawPhone) ?: null) : null;

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'telepon'           => $normalizedPhone,
            'password'          => Hash::make($validated['password']),
            'role'              => 'pelanggan',
            'email_verified_at' => null, // Bug 3 QA: email_verified_at default null tanpa auto-timestamp
        ]);

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

    /**
     * Normalisasi nomor telepon / WhatsApp Indonesia ke awalan 0 (mis. 081234567890).
     */
    private function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '' || $digits === null) {
            return '';
        }

        if (str_starts_with($digits, '620')) {
            return '0' . substr($digits, 3);
        }

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0' . $digits;
        }

        return $digits;
    }

    /**
     * Cari akun pengguna berdasarkan nomor WhatsApp / telepon dengan mendukung variasi format.
     */
    private function findUserByPhone(string $raw): ?User
    {
        $normalized = $this->normalizePhone($raw);
        if ($normalized === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?: $normalized;
        $intl = str_starts_with($normalized, '0') ? '62' . substr($normalized, 1) : $normalized;
        $candidates = array_values(array_unique(array_filter([
            trim($raw),
            $normalized,
            $digits,
            $intl,
            '+' . $intl,
        ])));

        $matched = User::whereIn('telepon', $candidates)->first();
        if ($matched !== null) {
            return $matched;
        }

        // Fallback aman untuk nomor tersimpan yang memakai tanda hubung/spasi
        return User::whereNotNull('telepon')
            ->get(['id', 'telepon'])
            ->first(fn (User $u) => $this->normalizePhone((string) $u->telepon) === $normalized);
    }
}

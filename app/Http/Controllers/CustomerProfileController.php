<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Shipping\OsmRouteShippingCostResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Pengelolaan profil dan alamat pengiriman utama pelanggan (TC-CHK-01).
 *
 * Menyimpan preferensi alamat default & koordinat pelanggan pada tabel `settings`
 * (key: customer_address_{user_id}) agar mematuhi Kontrak 10 tabel domain tanpa mengubah schema.
 */
class CustomerProfileController extends Controller
{
    public const DEFAULT_ALAMAT = 'Jl. Hayam Wuruk No. 42, Mergelo, Kec. Magersari, Kota Mojokerto';
    public const DEFAULT_PATOKAN = 'Pagar hitam depan apotek, seberang minimarket';
    public const DEFAULT_LAT = -7.472100;
    public const DEFAULT_LNG = 112.434000;

    /**
     * Menampilkan halaman profil pelanggan beserta alamat pengiriman utama.
     * Endpoint: GET /profil-pelanggan
     */
    public function show(): View
    {
        $user = Auth::user();
        $savedAddress = $this->resolveSavedAddress($user);

        $completedOrders = $user->orders()
            ->where('status', 'selesai')
            ->with('orderItems')
            ->get();

        $totalOrdersCount = $user->orders()->count();
        $totalBottlesCount = $completedOrders->sum(fn ($order) => $order->orderItems->sum('jumlah'));

        return view('Profil Pelanggan Page', compact(
            'savedAddress',
            'totalOrdersCount',
            'totalBottlesCount'
        ));
    }

    /**
     * Memperbarui alamat pengiriman utama dan koordinat peta pelanggan.
     * Endpoint: PUT /profil-pelanggan/alamat
     */
    public function updateAddress(Request $request, OsmRouteShippingCostResolver $resolver): RedirectResponse
    {
        $validated = $request->validate([
            'label'   => ['nullable', 'string', 'max:50'],
            'alamat'  => ['required', 'string', 'max:500'],
            'patokan' => ['nullable', 'string', 'max:255'],
            'lat'     => ['required', 'numeric', 'between:-90,90'],
            'lng'     => ['required', 'numeric', 'between:-180,180'],
        ], [
            'alamat.required' => 'Alamat lengkap pengiriman wajib diisi.',
            'lat.required'    => 'Titik koordinat lokasi di peta wajib ditentukan.',
            'lng.required'    => 'Titik koordinat lokasi di peta wajib ditentukan.',
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];

        // Validasi Geofence Wilayah Layanan: Kota Mojokerto (BR-8, FR-7.5)
        if (! $resolver->isInsideMojokertoCity($lat, $lng)) {
            return back()
                ->withInput()
                ->withErrors([
                    'lat' => 'Titik alamat pengiriman berada di luar wilayah layanan Kota Mojokerto. Silakan pilih lokasi di dalam Kota Mojokerto.',
                ])
                ->with('error', 'Titik alamat pengiriman berada di luar wilayah Kota Mojokerto!');
        }

        $user = Auth::user();

        $payload = [
            'label'   => trim((string) ($validated['label'] ?? '')) ?: 'Rumah / Kost',
            'alamat'  => trim((string) $validated['alamat']),
            'patokan' => trim((string) ($validated['patokan'] ?? '')),
            'lat'     => round($lat, 6),
            'lng'     => round($lng, 6),
        ];

        Setting::updateOrCreate(
            ['key' => 'customer_address_' . $user->id],
            [
                'value'       => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'description' => 'Alamat pengiriman utama pelanggan #' . $user->id,
            ]
        );

        return redirect()
            ->route('profile')
            ->with('success', 'Alamat pengiriman utama berhasil diperbarui dan otomatis digunakan saat checkout!');
    }

    /**
     * Mengambil alamat default pelanggan dari settings -> pesanan terakhir -> fallback Mojokerto.
     *
     * @return array{label: string, alamat: string, patokan: string, lat: float, lng: float}
     */
    private function resolveSavedAddress($user): array
    {
        $raw = Setting::where('key', 'customer_address_' . $user->id)->value('value');
        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        if (is_array($decoded) && ! empty($decoded['alamat']) && isset($decoded['lat'], $decoded['lng'])) {
            return [
                'label'   => (string) ($decoded['label'] ?? 'Rumah / Kost'),
                'alamat'  => (string) $decoded['alamat'],
                'patokan' => (string) ($decoded['patokan'] ?? ''),
                'lat'     => (float) $decoded['lat'],
                'lng'     => (float) $decoded['lng'],
            ];
        }

        $latestOrder = $user->orders()
            ->whereNotNull('alamat')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->latest()
            ->first();

        if ($latestOrder) {
            return [
                'label'   => 'Alamat Terakhir',
                'alamat'  => (string) $latestOrder->alamat,
                'patokan' => '',
                'lat'     => (float) $latestOrder->lat,
                'lng'     => (float) $latestOrder->lng,
            ];
        }

        return [
            'label'   => 'Rumah / Kost',
            'alamat'  => self::DEFAULT_ALAMAT,
            'patokan' => self::DEFAULT_PATOKAN,
            'lat'     => self::DEFAULT_LAT,
            'lng'     => self::DEFAULT_LNG,
        ];
    }
}

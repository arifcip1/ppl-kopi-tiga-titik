<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\ShippingLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingController extends Controller
{
    /**
     * Menghitung quote jarak & ongkir dinamis (FR-7.1, FR-7.2, FR-7.3, PPL 19 & PPL 25).
     * Endpoint: POST /api/shipping/quote
     */
    public function quote(Request $request): JsonResponse
    {
        $metodePemenuhan = $request->input('metode_pemenuhan', 'pengiriman');

        // Jika metode pemenuhan adalah ambil sendiri di lapak, ongkir Rp 0 (BR-41, FR-14)
        if ($metodePemenuhan === 'ambil_sendiri') {
            $activeLocationId = Setting::where('key', 'lokasi_pengiriman_aktif_id')->value('value');
            $lokasiAktif = $activeLocationId ? ShippingLocation::find($activeLocationId) : null;
            if (! $lokasiAktif) {
                $lokasiAktif = ShippingLocation::where('aktif', true)->first();
            }

            $jamBuka = Setting::where('key', 'jam_buka')->value('value') ?? '15:00';
            $jamTutup = Setting::where('key', 'jam_tutup')->value('value') ?? '18:00';

            return response()->json([
                'success' => true,
                'message' => 'Layanan ambil sendiri di lapak Kopi Tiga Titik bebas ongkir.',
                'data'    => [
                    'metode_pemenuhan' => 'ambil_sendiri',
                    'jarak_km'         => 0,
                    'jarak_formatted'  => '0 km',
                    'ongkir'           => 0,
                    'ongkir_formatted' => 'Rp 0',
                    'is_gratis'        => true,
                    'mata_uang'        => 'IDR',
                    'lokasi_asal'      => [
                        'id'          => $lokasiAktif?->id,
                        'nama_lokasi' => $lokasiAktif?->nama_lokasi ?? 'Lapak Kopi Tiga Titik',
                        'alamat'      => $lokasiAktif?->alamat ?? 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto',
                        'lat'         => $lokasiAktif ? (float) $lokasiAktif->latitude : -7.472600,
                        'lng'         => $lokasiAktif ? (float) $lokasiAktif->longitude : 112.438500,
                    ],
                    'jam_operasional'  => "{$jamBuka} - {$jamTutup} WIB",
                ],
            ], 200);
        }

        // Validasi koordinat tujuan pelanggan
        $lat = $request->input('lat') ?? $request->input('latitude_tujuan');
        $lng = $request->input('lng') ?? $request->input('longitude_tujuan');

        if ($lat === null || $lng === null || ! is_numeric($lat) || ! is_numeric($lng) || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return response()->json([
                'success' => false,
                'message' => 'Titik lokasi tujuan pengiriman wajib diisi dengan koordinat yang valid.',
                'errors'  => [
                    'lokasi_tujuan' => ['Koordinat latitude dan longitude pengiriman tidak valid.'],
                ],
            ], 422);
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        // Validasi Geofence Wilayah Layanan: Kota Mojokerto (BR-16, BR-33, BR-46, FR-7.5)
        // Koordinat batas administratif Kota Mojokerto:
        // Latitude: antara -7.525000 s/d -7.420000
        // Longitude: antara 112.390000 s/d 112.480000
        if (! $this->isInsideMojokertoCity($lat, $lng)) {
            return response()->json([
                'success' => false,
                'message' => 'Titik lokasi tujuan berada di luar jangkauan wilayah Kota Mojokerto.',
                'errors'  => [
                    'lokasi_tujuan' => ['Layanan pengantaran Kopi Tiga Titik saat ini hanya melayani wilayah Kota Mojokerto.'],
                ],
            ], 422);
        }

        // Ambil lokasi asal aktif (BR-34, BR-35, FR-11.4)
        $activeLocationId = Setting::where('key', 'lokasi_pengiriman_aktif_id')->value('value');
        $lokasiAktif = $activeLocationId ? ShippingLocation::find($activeLocationId) : null;
        if (! $lokasiAktif) {
            $lokasiAktif = ShippingLocation::where('aktif', true)->first();
        }

        $originLat = $lokasiAktif ? (float) $lokasiAktif->latitude : -7.472600;
        $originLng = $lokasiAktif ? (float) $lokasiAktif->longitude : 112.438500;

        // Hitung estimasi jarak rute jalan (menggunakan Haversine distance dengan road winding factor perkotaan 1.25)
        $straightDistance = $this->calculateHaversineDistance($originLat, $originLng, $lat, $lng);
        $routeDistance = round($straightDistance * 1.25, 2);

        // Minimal jarak estimasi 0.3 km
        if ($routeDistance < 0.3) {
            $routeDistance = 0.3;
        }

        // Formula Ongkir Bertingkat Resmi (BR-14, FR-7.3, PPL 01 Bagian 4.1):
        // d <= 5.5 km -> Rp 0 (Gratis promo lokal)
        // d > 5.5 km  -> Rp 2.000 * (floor(d - 5.5) + 1)
        if ($routeDistance <= 5.5) {
            $ongkir = 0;
            $isGratis = true;
            $keterangan = 'Gratis Ongkir (Promo jarak <= 5,5 km)';
        } else {
            $step = (int) floor($routeDistance - 5.5) + 1;
            $ongkir = 2000 * $step;
            $isGratis = false;
            $keterangan = "Jarak {$routeDistance} km (Kenaikan Rp 2.000 / km setelah 5,5 km)";
        }

        return response()->json([
            'success' => true,
            'message' => 'Ongkos kirim berhasil dihitung.',
            'data'    => [
                'metode_pemenuhan' => 'pengiriman',
                'jarak_km'         => $routeDistance,
                'jarak_formatted'  => "{$routeDistance} km",
                'ongkir'           => $ongkir,
                'ongkir_formatted' => $ongkir === 0 ? 'Gratis' : 'Rp ' . number_format($ongkir, 0, ',', '.'),
                'is_gratis'        => $isGratis,
                'formula_ket'      => $keterangan,
                'mata_uang'        => 'IDR',
                'lokasi_asal'      => [
                    'id'          => $lokasiAktif?->id,
                    'nama_lokasi' => $lokasiAktif?->nama_lokasi ?? 'Lapak Jl. Kuwung',
                    'alamat'      => $lokasiAktif?->alamat ?? 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto',
                    'lat'         => $originLat,
                    'lng'         => $originLng,
                ],
            ],
        ], 200);
    }

    /**
     * Tampilkan antarmuka pengaturan lokasi lapak aktif dan jam operasional untuk Owner (FR-11.1 s/d FR-11.7, PPL 25).
     * Endpoint: GET /owner/update-lokasi
     */
    public function showLocationSettings(): View
    {
        $locations = ShippingLocation::orderBy('id', 'desc')->get();

        $activeLocationId = Setting::where('key', 'lokasi_pengiriman_aktif_id')->value('value');
        $lokasiAktif = $activeLocationId ? ShippingLocation::find($activeLocationId) : null;
        if (! $lokasiAktif) {
            $lokasiAktif = $locations->firstWhere('aktif', true) ?? $locations->first();
        }

        $jamBuka = Setting::where('key', 'jam_buka')->value('value') ?? '15:00';
        $jamTutup = Setting::where('key', 'jam_tutup')->value('value') ?? '18:00';
        $statusLapak = Setting::where('key', 'status_lapak')->value('value') ?? 'buka';

        return view('Pop Up Update Lokasi Lapak Page', compact(
            'locations',
            'lokasiAktif',
            'jamBuka',
            'jamTutup',
            'statusLapak'
        ));
    }

    /**
     * Simpan pembaruan lokasi lapak aktif dan jam operasional oleh Owner (FR-11.4, FR-11.6, PPL 25).
     * Endpoint: POST /owner/update-lokasi
     */
    public function updateLocationSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lokasi'          => ['required', 'string', 'max:150'],
            'alamat'               => ['required', 'string', 'max:500'],
            'latitude'             => ['required', 'numeric', 'between:-90,90'],
            'longitude'            => ['required', 'numeric', 'between:-180,180'],
            'jam_buka'             => ['required', 'string', 'max:10'],
            'jam_tutup'            => ['required', 'string', 'max:10'],
            'status_lapak'         => ['nullable', 'string', 'in:buka,tutup'],
            'shipping_location_id' => ['nullable', 'integer', 'exists:shipping_locations,id'],
        ], [
            'nama_lokasi.required' => 'Nama patokan / tempat mangkal wajib diisi.',
            'alamat.required'      => 'Alamat lengkap lapak wajib diisi.',
            'latitude.required'    => 'Titik koordinat latitude lapak wajib ditentukan di peta.',
            'longitude.required'   => 'Titik koordinat longitude lapak wajib ditentukan di peta.',
            'jam_buka.required'    => 'Jam buka operasional wajib diisi.',
            'jam_tutup.required'   => 'Jam tutup operasional wajib diisi.',
        ]);

        // Simpan / perbarui ke tabel shipping_locations (BR-34, FR-11.1)
        if (! empty($validated['shipping_location_id'])) {
            $location = ShippingLocation::findOrFail($validated['shipping_location_id']);
            $location->update([
                'nama_lokasi' => $validated['nama_lokasi'],
                'alamat'      => $validated['alamat'],
                'latitude'    => $validated['latitude'],
                'longitude'   => $validated['longitude'],
                'aktif'       => true,
            ]);
        } else {
            $location = ShippingLocation::create([
                'nama_lokasi' => $validated['nama_lokasi'],
                'alamat'      => $validated['alamat'],
                'latitude'    => $validated['latitude'],
                'longitude'   => $validated['longitude'],
                'aktif'       => true,
            ]);
        }

        // Set ID lokasi aktif ke tabel settings (BR-35, FR-11.4)
        Setting::updateOrCreate(
            ['key' => 'lokasi_pengiriman_aktif_id'],
            [
                'value'       => (string) $location->id,
                'description' => 'ID lokasi lapak pengiriman aktif.',
            ]
        );

        // Update jam operasional (FR-11.6)
        Setting::updateOrCreate(
            ['key' => 'jam_buka'],
            [
                'value'       => $validated['jam_buka'],
                'description' => 'Jam operasional buka lapak.',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'jam_tutup'],
            [
                'value'       => $validated['jam_tutup'],
                'description' => 'Jam operasional tutup lapak.',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'status_lapak'],
            [
                'value'       => $validated['status_lapak'] ?? 'buka',
                'description' => 'Status buka/tutup lapak hari ini.',
            ]
        );

        return redirect()->route('owner.update-lokasi')->with('success', 'Lokasi lapak aktif dan jam operasional berhasil diperbarui!');
    }

    /**
     * Pilih lokasi asal yang sudah tersimpan menjadi lokasi aktif (FR-11.4).
     * Endpoint: PATCH /owner/lokasi/{location}/select
     */
    public function selectLocation(ShippingLocation $location): RedirectResponse
    {
        if (! $location->aktif) {
            return redirect()->back()->with('error', 'Lokasi yang dinonaktifkan tidak dapat dipilih sebagai lokasi aktif.');
        }

        Setting::updateOrCreate(
            ['key' => 'lokasi_pengiriman_aktif_id'],
            [
                'value'       => (string) $location->id,
                'description' => 'ID lokasi lapak pengiriman aktif.',
            ]
        );

        return redirect()->back()->with('success', "Lokasi aktif berhasil dialihkan ke {$location->nama_lokasi}.");
    }

    /**
     * Helper perhitungan jarak lingkaran besar (Haversine formula) dalam kilometer.
     */
    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * Helper validasi batas wilayah geografis Kota Mojokerto (BR-16, BR-33, BR-46).
     */
    private function isInsideMojokertoCity(float $lat, float $lng): bool
    {
        return $lat >= -7.530000 && $lat <= -7.420000 &&
               $lng >= 112.390000 && $lng <= 112.480000;
    }
}


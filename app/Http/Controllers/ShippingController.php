<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Services\Shipping\OsmRouteShippingCostResolver;
use App\Services\Shipping\ShippingCostUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingController extends Controller
{
    public function __construct(protected OsmRouteShippingCostResolver $resolver)
    {
    }

    /**
     * Menghitung quote jarak & ongkir dinamis (FR-7.1, FR-7.2, FR-7.3, PPL 19 & PPL 25 & PPL 26).
     * Endpoint: POST /api/shipping/quote
     */
    public function quote(Request $request): JsonResponse
    {
        $metodePemenuhan = $request->input('metode_pemenuhan', 'pengiriman');

        // Jika metode pemenuhan adalah ambil sendiri di lapak, ongkir Rp 0 (BR-41, FR-14)
        if ($metodePemenuhan === 'ambil_sendiri') {
            try {
                $quote = $this->resolver->calculateQuote(0.0, 0.0, 'ambil_sendiri');

                return response()->json([
                    'success' => true,
                    'message' => 'Layanan ambil sendiri di lapak Kopi Tiga Titik bebas ongkir.',
                    'data'    => $quote,
                ], 200);
            } catch (ShippingCostUnavailableException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors'  => [
                        'lokasi_tujuan' => [$e->getMessage()],
                    ],
                ], 422);
            }
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

        try {
            $quote = $this->resolver->calculateQuote($lat, $lng, 'pengiriman');

            return response()->json([
                'success' => true,
                'message' => 'Ongkos kirim berhasil dihitung.',
                'data'    => $quote,
            ], 200);
        } catch (ShippingCostUnavailableException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => [
                    'lokasi_tujuan' => [$e->getMessage()],
                ],
            ], 422);
        }
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
}


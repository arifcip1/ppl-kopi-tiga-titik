<?php

namespace App\Services\Shipping;

use App\Models\Setting;
use App\Models\ShippingLocation;

/**
 * Resolver ongkos kirim berbasis rute jalan OpenStreetMap / OpenRouteService (PPL-26, F7, FR-7.1 s/d FR-7.7).
 */
class OsmRouteShippingCostResolver extends UnavailableShippingCostResolver
{
    public function __construct(protected OsmRoutingService $routingService)
    {
    }

    public function getRoutingService(): OsmRoutingService
    {
        return $this->routingService;
    }

    /**
     * Hitung ongkos kirim (rupiah, integer) dari titik tujuan pengantaran (FR-7.3).
     *
     * @throws ShippingCostUnavailableException jika di luar Mojokerto, lapak tutup, atau routing gagal.
     */
    public function resolve(float $lat, float $lng): int
    {
        $quote = $this->calculateQuote($lat, $lng, 'pengiriman');

        return $quote['ongkir'];
    }

    /**
     * Kalkulasi lengkap quote pengiriman meliputi jarak rute, ongkos kirim bertingkat, dan snapshot lokasi lapak.
     *
     * @throws ShippingCostUnavailableException
     */
    public function calculateQuote(float $lat, float $lng, string $metodePemenuhan = 'pengiriman'): array
    {
        // 1. Ambil lokasi asal aktif dari database (BR-34, FR-11.4)
        $activeLocationId = Setting::where('key', 'lokasi_pengiriman_aktif_id')->value('value');
        $lokasiAktif = $activeLocationId ? ShippingLocation::find($activeLocationId) : null;
        if (! $lokasiAktif) {
            $lokasiAktif = ShippingLocation::where('aktif', true)->first();
        }

        if (! $lokasiAktif) {
            throw new ShippingCostUnavailableException('Fitur perhitungan ongkir belum tersedia: lokasi lapak aktif belum dikonfigurasi.');
        }

        $originLat = (float) $lokasiAktif->latitude;
        $originLng = (float) $lokasiAktif->longitude;

        $operational = app(\App\Services\LapakOperationalService::class)->getStatus();
        $jamBuka = $operational['jam_buka'];
        $jamTutup = $operational['jam_tutup'];
        $statusLapak = $operational['effective_status'];

        // 2. Validasi Status & Jam Operasional Lapak untuk SEMUA checkout online baru (FR-11.6, BR-10)
        if (! $operational['is_open']) {
            if ($operational['closed_reason'] === 'manual' && $metodePemenuhan === 'pengiriman') {
                throw new ShippingCostUnavailableException(
                    'Lapak Kopi Tiga Titik saat ini sedang tutup. Pemesanan pengantaran kurir tidak dapat diproses.'
                );
            }

            throw new ShippingCostUnavailableException(
                $operational['closed_message']
                    ?? 'Lapak Kopi Tiga Titik saat ini sedang tutup. Pemesanan online tidak dapat diproses.'
            );
        }

        // 3. Jika metode ambil sendiri di lapak, ongkir Rp 0 (BR-11, FR-13)
        if ($metodePemenuhan === 'ambil_sendiri') {
            return [
                'metode_pemenuhan' => 'ambil_sendiri',
                'jarak_km'         => 0.0,
                'jarak_formatted'  => '0 km',
                'ongkir'           => 0,
                'ongkir_formatted' => 'Rp 0',
                'is_gratis'        => true,
                'formula_ket'      => 'Bebas Ongkir (Ambil Sendiri di Lapak)',
                'mata_uang'        => 'IDR',
                'lokasi_asal'      => [
                    'id'          => $lokasiAktif->id,
                    'nama_lokasi' => $lokasiAktif->nama_lokasi,
                    'alamat'      => $lokasiAktif->alamat,
                    'lat'         => $originLat,
                    'lng'         => $originLng,
                ],
                'jam_operasional'  => "{$jamBuka} - {$jamTutup} WIB",
                'status_lapak'     => $statusLapak,
            ];
        }

        // 4. Validasi Geofence Wilayah Layanan: Kota Mojokerto (BR-8, FR-7.5)
        if (! $this->isInsideMojokertoCity($lat, $lng)) {
            throw new ShippingCostUnavailableException('Titik lokasi tujuan berada di luar jangkauan wilayah Kota Mojokerto.');
        }

        // 5. Panggil OpenRouteService untuk mendapatkan jarak rute jalan riil (FR-7.2)
        $routeDistanceKm = $this->routingService->getRouteDistance($originLat, $originLng, $lat, $lng);

        // 6. Formula Ongkir Bertingkat Resmi (BR-14, FR-7.3, PPL 01 Bagian 4.1):
        // d <= 5.5 km -> Rp 0 (Gratis promo lokal)
        // d > 5.5 km  -> Rp 2.000 * (floor(d - 5.5) + 1)
        if ($routeDistanceKm <= 5.50) {
            $ongkir = 0;
            $isGratis = true;
            $keterangan = 'Gratis Ongkir (Promo jarak <= 5,5 km)';
        } else {
            $step = (int) floor($routeDistanceKm - 5.50) + 1;
            $ongkir = 2000 * $step;
            $isGratis = false;
            $keterangan = "Jarak {$routeDistanceKm} km (Kenaikan Rp 2.000 / km setelah 5,5 km)";
        }

        return [
            'metode_pemenuhan' => 'pengiriman',
            'jarak_km'         => $routeDistanceKm,
            'jarak_formatted'  => "{$routeDistanceKm} km",
            'ongkir'           => $ongkir,
            'ongkir_formatted' => $ongkir === 0 ? 'Gratis' : 'Rp ' . number_format($ongkir, 0, ',', '.'),
            'is_gratis'        => $isGratis,
            'formula_ket'      => $keterangan,
            'mata_uang'        => 'IDR',
            'lokasi_asal'      => [
                'id'          => $lokasiAktif->id,
                'nama_lokasi' => $lokasiAktif->nama_lokasi,
                'alamat'      => $lokasiAktif->alamat,
                'lat'         => $originLat,
                'lng'         => $originLng,
            ],
            'jam_operasional'  => "{$jamBuka} - {$jamTutup} WIB",
            'status_lapak'     => $statusLapak,
        ];
    }

    /**
     * Helper validasi batas administratif wilayah geografis Kota Mojokerto (FR-7.5).
     */
    public function isInsideMojokertoCity(float $lat, float $lng): bool
    {
        return $lat >= -7.530000 && $lat <= -7.420000 &&
               $lng >= 112.390000 && $lng <= 112.480000;
    }
}


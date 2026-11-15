<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service integrasi OpenRouteService (ORS) / OpenStreetMap untuk kalkulasi jarak rute jalan (PPL-26, F7, BR-7).
 */
class OsmRoutingService
{
    private ?string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private string $profile;
    private bool $allowSimulation;

    public function __construct()
    {
        $this->apiKey = config('services.openrouteservice.api_key') ?: env('OPENROUTESERVICE_API_KEY');
        $this->baseUrl = rtrim(config('services.openrouteservice.base_url') ?: 'https://api.openrouteservice.org', '/');
        $this->timeout = (int) (config('services.openrouteservice.timeout') ?: 5);
        $this->profile = config('services.openrouteservice.profile') ?: 'driving-car';
        $simConfig = config('services.openrouteservice.simulation');
        $this->allowSimulation = $simConfig !== null
            ? (bool) $simConfig
            : (bool) env('OPENROUTESERVICE_SIMULATION', app()->environment('local', 'testing'));
    }

    /**
     * Memperoleh jarak rute jalan riil dalam kilometer dari koordinat asal ke tujuan.
     *
     * @throws ShippingCostUnavailableException jika rute tidak ditemukan, layanan timeout, kuota habis, atau API key tidak valid.
     */
    public function getRouteDistance(float $originLat, float $originLng, float $destLat, float $destLng): float
    {
        $this->validateCoordinates($originLat, $originLng);
        $this->validateCoordinates($destLat, $destLng);

        // Jika jarak sangat dekat (titik sama persis), kembalikan jarak minimum 0.1 km
        if (abs($originLat - $destLat) < 0.00001 && abs($originLng - $destLng) < 0.00001) {
            return 0.10;
        }

        // Jika API Key tidak tersedia
        if (empty($this->apiKey)) {
            if ($this->allowSimulation) {
                return $this->calculateSimulatedRoadDistance($originLat, $originLng, $destLat, $destLng);
            }

            Log::error('[ORS Routing] API Key OpenRouteService belum dikonfigurasi di environment (OPENROUTESERVICE_API_KEY).');
            throw new ShippingCostUnavailableException('Fitur perhitungan rute pengiriman belum dikonfigurasi pada server (API Key ORS belum diatur).');
        }

        $endpoint = "{$this->baseUrl}/v2/directions/{$this->profile}";

        try {
            // Format koordinat GeoJSON ORS adalah [longitude, latitude]
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                    'Accept'        => 'application/json, application/geo+json',
                    'Content-Type'  => 'application/json',
                ])
                ->post($endpoint, [
                    'coordinates' => [
                        [$originLng, $originLat],
                        [$destLng, $destLat],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // ORS GeoJSON format: features[0].properties.summary.distance (dalam meter)
                $distanceMeters = $data['features'][0]['properties']['summary']['distance']
                    ?? $data['routes'][0]['summary']['distance']
                    ?? null;

                if ($distanceMeters === null || ! is_numeric($distanceMeters)) {
                    Log::error('[ORS Routing] Respons sukses namun tidak menemukan nilai jarak rute valid.', [
                        'response' => $data,
                    ]);
                    throw new ShippingCostUnavailableException('Layanan rute jalan tidak mengembalikan data jarak pengiriman yang valid.');
                }

                // Konversi meter ke kilometer (2 desimal presisi)
                $distanceKm = round(((float) $distanceMeters) / 1000.0, 2);

                return max(0.10, $distanceKm);
            }

            // Tangani error respons dari server ORS (401, 403, 429, 404/2004, dll)
            $statusCode = $response->status();
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? $errorBody['message'] ?? 'Gagal menghubungi routing engine ORS.';

            Log::error("[ORS Routing] Permintaan rute ke ORS gagal dengan HTTP {$statusCode}.", [
                'status'  => $statusCode,
                'message' => $errorMessage,
            ]);

            if ($statusCode === 429) {
                throw new ShippingCostUnavailableException('Batas kuota layanan rute jalan telah terlampaui. Silakan coba beberapa saat lagi.');
            }

            if ($statusCode === 401 || $statusCode === 403) {
                throw new ShippingCostUnavailableException('Otorisasi layanan rute jalan tidak valid atau akun dinonaktifkan.');
            }

            throw new ShippingCostUnavailableException('Rute jalan menuju titik tujuan tidak dapat dihitung oleh sistem.');

        } catch (\Illuminate\Http\Client\ConnectionException|\Illuminate\Http\Client\RequestException $e) {
            Log::error('[ORS Routing] Gangguan koneksi atau timeout saat menghubungi OpenRouteService.', [
                'error' => $e->getMessage(),
            ]);

            throw new ShippingCostUnavailableException('Layanan rute jalan sedang mengalami timeout atau gangguan koneksi. Silakan coba lagi.');
        }
    }

    /**
     * Konfigurasi runtime API Key (berguna untuk testing).
     */
    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Konfigurasi runtime simulasi jarak jalan (berguna untuk testing/dev).
     */
    public function setAllowSimulation(bool $allowSimulation): self
    {
        $this->allowSimulation = $allowSimulation;

        return $this;
    }

    /**
     * Validasi rentang koordinat geografis bumi.
     */
    private function validateCoordinates(float $lat, float $lng): void
    {
        if ($lat < -90.0 || $lat > 90.0 || $lng < -180.0 || $lng > 180.0) {
            throw new ShippingCostUnavailableException("Koordinat titik geografis tidak valid: Lat {$lat}, Lng {$lng}.");
        }
    }

    /**
     * Simulasi jarak rute jalan (Haversine * 1.25 urban road factor) jika mode simulasi diizinkan.
     */
    public function calculateSimulatedRoadDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $straightDistance = $earthRadiusKm * $c;

        return max(0.10, round($straightDistance * 1.25, 2));
    }
}

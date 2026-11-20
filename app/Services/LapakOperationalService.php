<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Layanan terpusat untuk mengevaluasi status operasional lapak (FR-11.6, BR-10, BR-22).
 *
 * Menggabungkan pengaturan manual (`status_lapak`) dan rentang jam operasional
 * (`jam_buka` - `jam_tutup`) berdasarkan waktu saat ini pada zona waktu Asia/Jakarta (WIB).
 */
class LapakOperationalService
{
    public const TIMEZONE = 'Asia/Jakarta';

    /**
     * Mengambil status operasional lapak yang efektif saat ini.
     *
     * @return array{
     *     jam_buka: string,
     *     jam_tutup: string,
     *     manual_status: string,
     *     effective_status: string,
     *     is_open: bool,
     *     is_within_hours: bool,
     *     is_outside_hours: bool,
     *     closed_reason: string|null,
     *     closed_message: string|null,
     *     jam_operasional: string
     * }
     */
    public function getStatus(?Carbon $now = null): array
    {
        $jamBukaDb = null;
        $jamTutupDb = null;
        $manualStatus = 'buka';

        if (Schema::hasTable('settings')) {
            $settings = Setting::whereIn('key', ['jam_buka', 'jam_tutup', 'status_lapak'])
                ->pluck('value', 'key');

            $jamBukaDb = $settings->get('jam_buka');
            $jamTutupDb = $settings->get('jam_tutup');
            $manualStatus = $settings->get('status_lapak') ?? 'buka';
        }

        $hasConfiguredHours = ($jamBukaDb !== null && trim((string) $jamBukaDb) !== '')
            && ($jamTutupDb !== null && trim((string) $jamTutupDb) !== '');

        $jamBuka = $hasConfiguredHours ? $this->normalizeTime((string) $jamBukaDb, '15:00') : '15:00';
        $jamTutup = $hasConfiguredHours ? $this->normalizeTime((string) $jamTutupDb, '18:00') : '18:00';

        $current = ($now ? $now->copy() : Carbon::now(self::TIMEZONE))->setTimezone(self::TIMEZONE);

        $isWithinHours = $hasConfiguredHours
            ? $this->isTimeWithinRange($current, $jamBuka, $jamTutup)
            : true;

        $isManuallyClosed = $manualStatus === 'tutup';
        $isOpen = ! $isManuallyClosed && $isWithinHours;

        $closedReason = null;
        $closedMessage = null;

        if ($isManuallyClosed) {
            $closedReason = 'manual';
            $closedMessage = 'Lapak Kopi Tiga Titik saat ini sedang tutup. Pemesanan online baru (antar kurir maupun ambil sendiri) dapat dilakukan saat lapak buka kembali.';
        } elseif (! $isWithinHours) {
            $closedReason = 'outside_hours';
            $closedMessage = "Lapak Kopi Tiga Titik saat ini sedang tutup karena di luar jam operasional ({$jamBuka} - {$jamTutup} WIB). Pemesanan online baru (antar kurir maupun ambil sendiri) hanya dapat dilakukan pada jam operasional.";
        }

        return [
            'jam_buka'         => $jamBuka,
            'jam_tutup'        => $jamTutup,
            'manual_status'    => $manualStatus,
            'effective_status' => $isOpen ? 'buka' : 'tutup',
            'is_open'          => $isOpen,
            'is_within_hours'  => $isWithinHours,
            'is_outside_hours' => ! $isWithinHours,
            'closed_reason'    => $closedReason,
            'closed_message'   => $closedMessage,
            'jam_operasional'  => "{$jamBuka} - {$jamTutup} WIB",
        ];
    }

    /**
     * Mengecek apakah waktu saat ini berada dalam rentang jam buka - jam tutup (inklusif).
     * Mendukung rentang normal (mis. 08:00 - 18:00) maupun lintas tengah malam (mis. 20:00 - 02:00).
     */
    public function isTimeWithinRange(Carbon $current, string $jamBuka, string $jamTutup): bool
    {
        $openMinutes = $this->toMinutes($jamBuka);
        $closeMinutes = $this->toMinutes($jamTutup);
        $nowMinutes = ((int) $current->format('H')) * 60 + ((int) $current->format('i'));

        if ($openMinutes === null || $closeMinutes === null) {
            return true;
        }

        if ($openMinutes <= $closeMinutes) {
            return $nowMinutes >= $openMinutes && $nowMinutes <= $closeMinutes;
        }

        // Rentang lintas tengah malam (misal 20:00 - 02:00)
        return $nowMinutes >= $openMinutes || $nowMinutes <= $closeMinutes;
    }

    private function normalizeTime(string $value, string $fallback): string
    {
        if (preg_match('/^(\d{1,2}):(\d{2})/', trim($value), $matches)) {
            $h = str_pad((string) min(23, max(0, (int) $matches[1])), 2, '0', STR_PAD_LEFT);
            $m = str_pad((string) min(59, max(0, (int) $matches[2])), 2, '0', STR_PAD_LEFT);

            return "{$h}:{$m}";
        }

        return $fallback;
    }

    private function toMinutes(string $hhmm): ?int
    {
        if (! preg_match('/^(\d{1,2}):(\d{2})/', trim($hhmm), $matches)) {
            return null;
        }

        return ((int) $matches[1]) * 60 + ((int) $matches[2]);
    }
}

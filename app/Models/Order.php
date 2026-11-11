<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode',
        'sumber_transaksi',
        'metode_pemenuhan',
        'user_id',
        'courier_id',
        'status',
        'subtotal',
        'ongkir',
        'total',
        'nama_pelanggan',
        'nama_penerima',
        'telepon',
        'alamat',
        'lat',
        'lng',
        'nama_lokasi_asal',
        'alamat_lokasi_asal',
        'latitude_lokasi_asal',
        'longitude_lokasi_asal',
        'jarak_pengiriman',
        'batas_pengambilan',
        'catatan',
        'stok_dikembalikan',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'ongkir' => 'integer',
            'total' => 'integer',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'stok_dikembalikan' => 'boolean',
        ];
    }

    /**
     * Menghasilkan kode pesanan dengan format:
     * KTT-YYYYMMDD-NNNN
     */
    public static function generateOrderCode(): string
    {
        $prefix = 'KTT-' . now()->format('Ymd') . '-';

        $lastCode = static::query()
            ->where('kode', 'like', $prefix . '%')
            ->orderByDesc('kode')
            ->value('kode');

        $lastNumber = $lastCode
            ? (int) substr($lastCode, -4)
            : 0;

        $nextNumber = $lastNumber + 1;

        return $prefix . str_pad(
            (string) $nextNumber,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Waktu kedaluwarsa pembayaran.
     * Sumber utama: payments.kedaluwarsa_pada.
     * Fallback: created_at + 10 menit jika Payment belum tersedia.
     */
    public function getExpiresAtAttribute()
    {
        $payment = $this->payment;

        if ($payment !== null) {
            return $payment->kedaluwarsa_pada;
        }

        return $this->created_at?->copy()->addMinutes(10);
    }

    // =========================
    // HELPER STATUS PESANAN
    // =========================

    public function isWaitingPayment(): bool
    {
        return $this->status === 'menunggu_pembayaran';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'diproses';
    }

    public function isShipped(): bool
    {
        return $this->status === 'dikirim';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'selesai';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'dibatalkan';
    }

    /**
     * Memeriksa apakah pelanggan masih dapat membatalkan pesanan.
     */
    public function canBeCancelledByCustomer(): bool
    {
        if (!$this->isWaitingPayment()) {
            return false;
        }

        $payment = $this->payment;

        if ($payment !== null && $payment->isSuccess()) {
            return false;
        }

        $expiresAt = $this->expires_at;

        if ($expiresAt === null) {
            return false;
        }

        return now()->lt($expiresAt);
    }

    // =========================
    // RELASI ELOQUENT
    // =========================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items(): HasMany
    {
        return $this->orderItems();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
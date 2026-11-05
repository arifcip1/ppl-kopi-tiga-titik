<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'referensi_provider',
        'status',
        'jumlah',
        'kedaluwarsa_pada',
        'waktu_verifikasi',
        'payload_mentah',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'kedaluwarsa_pada' => 'datetime',
            'waktu_verifikasi' => 'datetime',
            'payload_mentah' => 'array',
        ];
    }

    // =========================
    // HELPER STATUS PEMBAYARAN
    // =========================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccess(): bool
    {
        return $this->status === 'berhasil';
    }

    public function isExpired(): bool
    {
        return $this->status === 'kedaluwarsa';
    }

    public function isFailed(): bool
    {
        return $this->status === 'gagal';
    }

    // =========================
    // RELASI ELOQUENT
    // =========================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
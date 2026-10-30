<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

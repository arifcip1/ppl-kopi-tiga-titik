<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'kode',
        'user_id',
        'courier_id',
        'status',
        'subtotal',
        'ongkir',
        'total',
        'nama_penerima',
        'telepon',
        'alamat',
        'lat',
        'lng',
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

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'nama',
        'rasa',
        'komposisi',
        'deskripsi',
        'keawetan',
        'harga',
        'stok',
        'foto_path',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'stok' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function salesHistories(): HasMany
    {
        return $this->hasMany(SalesHistory::class);
    }
}

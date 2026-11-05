<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
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

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto_path) {
            return asset('storage/' . $this->foto_path);
        }
        if (file_exists(public_path('products/' . $this->nama . '.png'))) {
            return asset('products/' . $this->nama . '.png');
        }
        return 'https://placehold.co/150x200/1e3a8a/ffffff?text=' . urlencode($this->nama);
    }
}

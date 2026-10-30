<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Keranjang milik pengguna
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // Pesanan yang dibuat sebagai pelanggan
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Pesanan yang ditugaskan kepada pengguna sebagai kurir
    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    // Riwayat perubahan status yang dilakukan pengguna
    public function orderStatusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}

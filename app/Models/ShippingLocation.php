<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingLocation extends Model
{
    protected $fillable = [
        'nama_lokasi',
        'latitude',
        'longitude',
        'alamat',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'aktif' => 'boolean',
        ];
    }
}

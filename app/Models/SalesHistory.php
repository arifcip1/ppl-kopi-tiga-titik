<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesHistory extends Model
{
    protected $fillable = [
        'product_id',
        'tanggal',
        'jumlah_terjual',
        'sumber',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah_terjual' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

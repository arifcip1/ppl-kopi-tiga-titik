<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $table = 'order_status_history';
    protected $fillable = [
        'order_id',
        'status_baru',
        'diubah_oleh',
        'waktu_perubahan',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_perubahan' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}

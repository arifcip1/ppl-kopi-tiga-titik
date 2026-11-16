<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler Pembatalan Pesanan Kedaluwarsa Setiap Menit (FR-6.8 / PPL-27)
Schedule::command('orders:expire')->everyMinute();


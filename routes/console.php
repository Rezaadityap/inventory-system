<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jaring pengaman kalau notifikasi Midtrans tidak sampai (tunnel mati, dsb).
Schedule::command('invoices:sync-payments')->everyFifteenMinutes()->withoutOverlapping();

Schedule::command('invoices:expire-overdue')->dailyAt('00:15');

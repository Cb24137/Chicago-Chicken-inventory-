<?php

use App\Services\AlertService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sends any low-stock and expiry alerts that are due. This also runs
// automatically on the first dashboard visit each day, because XAMPP
// does not run Laravel's scheduler.
Artisan::command('inventory:check-alerts', function (AlertService $alerts) {
    $sent = $alerts->runDailyChecks(force: true);
    $this->info("{$sent} notification(s) sent.");
})->purpose('Send low-stock and expiry alerts that are due');

Schedule::command('inventory:check-alerts')->dailyAt('07:00');

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Phase 12 — Scheduler
Schedule::command('ai:run-schedules')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('system:health-check')->hourly()->withoutOverlapping();

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

// Real Phase 2 — Blog pipeline publication
Schedule::command('blog:publish-due')->everyMinute()->withoutOverlapping();

// Real Phase 5 — Agent routines
Schedule::command('agents:run-due-routines')->everyMinute()->withoutOverlapping();

// Real Phase Social — Instagram publishing
Schedule::command('social:publish-due')->everyMinute()->withoutOverlapping();

// Real Phase Social — Instagram token auto-renewal (daily at 03:00)
Schedule::command('instagram:refresh-tokens')->dailyAt('03:00')->withoutOverlapping();

// Real Phase Email Notifications — Approval reminders (hourly)
Schedule::command('approvals:send-pending-reminders')->hourly()->withoutOverlapping();

// Real AI Employee Engine — Agent tasks
Schedule::command('agents:run-due-tasks')->everyMinute()->withoutOverlapping();

// Real Phase Threads — only schedule if threads_publishing_scheduler feature is enabled
// (threads:publish-due itself also guards against THREADS_PUBLISHING_ENABLED=false)
if (config('features.threads_publishing_scheduler', false)) {
    Schedule::command('threads:publish-due')->everyMinute()->withoutOverlapping()->runInBackground();
}

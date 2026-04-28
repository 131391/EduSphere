<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Apply late fees daily at 00:15
Schedule::command('fees:apply-late')->dailyAt('00:15')->withoutOverlapping();
Schedule::command('fees:generate-facility')->monthlyOn(1, '01:00')->withoutOverlapping();
Schedule::command('fees:send-reminders')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('library:notify-overdue')->dailyAt('07:00')->withoutOverlapping();
Schedule::command('exams:sync-statuses')->dailyAt('00:30')->withoutOverlapping();

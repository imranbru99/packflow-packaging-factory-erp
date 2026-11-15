<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('packflow:scan-stock')->dailyAt('07:00');
Schedule::command('packflow:maintenance-due')->dailyAt('06:30');
Schedule::command('packflow:email-reports')->weeklyOn(1, '08:00');

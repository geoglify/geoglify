<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('dosomething A --duration=15 --worker_id=2')->everyFiveSeconds()->runInBackground()->withoutOverlapping();

Schedule::command('dosomething B --duration=15 --worker_id=2')->everyFiveSeconds()->runInBackground()->withoutOverlapping();

Schedule::command('dosomething C --duration=15 --worker_id=2')->everyFiveSeconds()->runInBackground()->withoutOverlapping();

Schedule::command('dosomething D --duration=15 --worker_id=2')->everyFiveSeconds()->runInBackground()->withoutOverlapping();

Schedule::command('dosomething E --duration=15 --worker_id=2')->everyFiveSeconds()->runInBackground()->withoutOverlapping();

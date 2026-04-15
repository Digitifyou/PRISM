<?php

use App\Console\Commands\PublishScheduledPosts;
use Illuminate\Support\Facades\Schedule;

Schedule::command(PublishScheduledPosts::class)->everyMinute();

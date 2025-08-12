<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\GenerateSitemaps;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap:generate', function () {
    GenerateSitemaps::dispatch();
    $this->info('Sitemap generation job dispatched.');
})->purpose('Generate sitemap files for questions, categories, tags, and authors');

<?php

// Usage: $client = new SerpScraperClient(SerpScraperConfig::fromArray(config('serpscraper')));
// Publish: php artisan vendor:publish --tag=serpscraper-config

return [

    // API token — https://serpscraper.dev
    'token'    => env('SERPSCRAPER_TOKEN', env('SERPAPI_TOKEN', '')),

    // Default country code (gl). See config/serpscraper-countries.php for all values.
    'country'  => env('SERPSCRAPER_COUNTRY', env('SERPAPI_COUNTRY', 'US')),

    // Default language code (hl). See config/serpscraper-languages.php for all values.
    'language' => env('SERPSCRAPER_LANGUAGE', env('SERPAPI_LANGUAGE', 'en')),

    // cURL timeout in seconds.
    'timeout'  => (int) env('SERPSCRAPER_TIMEOUT', env('SERPAPI_TIMEOUT', 30)),

    // Override only for self-hosted / testing.
    'base_url' => env('SERPSCRAPER_BASE_URL', env('SERPAPI_BASE_URL', 'https://serpscraper.dev/api/v1')),

];

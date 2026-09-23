# serpscraper-php: Official PHP SDK for SerpScraper.Dev (Google SERP API, AI Overview & Trends)

<p align="center">
  <a href="https://serpscraper.dev"><img src="https://serpscraper.dev/img/logo.png" width="100" alt="SerpScraper.Dev Logo"></a>
</p>

<p align="center">
  Official <strong>PHP</strong> client library for <a href="https://serpscraper.dev"><strong>SerpScraper.Dev</strong></a> — a fast <a href="https://serpscraper.dev/serp-api">SERP API</a> for real-time Google search rankings, <a href="https://serpscraper.dev/google-ai-overview-api">Google AI Overviews</a>, and <a href="https://serpscraper.dev/google-trends-api">Google Trends</a>.
</p>

<p align="center">
  <a href="https://packagist.org/packages/tienphat/serpscraper-php"><img src="https://img.shields.io/packagist/v/tienphat/serpscraper-php.svg" alt="Latest Version on Packagist"></a>
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php" alt="PHP 8.1+">
  <img src="https://img.shields.io/badge/Dependencies-Zero-success" alt="Zero Dependencies">
  <a href="../LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="MIT License"></a>
  <a href="https://serpscraper.dev"><img src="https://img.shields.io/badge/API%20Key-Free%20Tier-green" alt="Free API Key"></a>
</p>

---

## ⚡ Why Use `tienphat/serpscraper-php`?

- 🏎️ **Zero Runtime Dependencies**: Built with pure PHP 8.1+ and native cURL. No Guzzle, no conflict with existing HTTP clients in your project.
- 🛡️ **Bypass Anti-Bot & CAPTCHAs**: Powered by [SerpScraper.Dev](https://serpscraper.dev) residential proxies to scrape Google without IP bans.
- 🤖 **Built-In Google AI Overview Parsing**: Directly access synthesized AI answers and citation links with `$response->summary()` and `$response->sources()` via the [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api).
- 📈 **Real-Time Google Trends**: Fetch live trending queries and historical interest timelines using the [Google Trends API](https://serpscraper.dev/google-trends-api).
- 🌍 **Global Geotargeting**: Search from 190+ countries (`gl`) and all languages (`hl`).
- 🔄 **Laravel Ready**: Includes service provider, facade (`SerpScraper::googleSearch()`), and config publication.
- 🤝 **Backward Compatible**: Includes `class_alias` support for seamless migration from legacy `SerpApiOrg` packages.

---

## 📦 Installation

Install via [Composer](https://getcomposer.org/):

```bash
composer require tienphat/serpscraper-php
```

---

## 🚀 Quick Start

Get your free API key at [serpscraper.dev](https://serpscraper.dev).

### 1. Google Web Search & AI Overview Extraction

```php
use SerpScraper\SerpScraperClient;

$client = new SerpScraperClient('YOUR_API_KEY');

// Execute Google search targeting United States English results
$results = $client->googleSearch('best ai search engines 2025', [
    'gl' => 'US',
    'hl' => 'en',
    'limit' => 10,
]);

// 1. Loop through Organic Search Rankings
echo "Fetched " . count($results) . " organic results in {$results->inSeconds()}s:\n\n";

foreach ($results as $item) {
    echo "#{$item['position']} {$item['title']}\n";
    echo "    URL: {$item['link']}\n";
    echo "    Snippet: {$item['snippet']}\n\n";
}

// 2. Extract Google AI Overview (SGE) & Sources
if ($summary = $results->summary()) {
    echo "──────────────────────────────────────────\n";
    echo "🤖 [Google AI Overview]:\n{$summary}\n";
    echo "──────────────────────────────────────────\n";
    echo "Cited Sources:\n";
    foreach ($results->sources() as $src) {
        echo " - {$src['title']} ({$src['link']})\n";
    }
}
```

---

### 2. Google Trends Real-Time & Interest Over Time

Track market trends and trending searches in real-time via the [Google Trends API](https://serpscraper.dev/google-trends-api):

```php
use SerpScraper\SerpScraperClient;

$client = new SerpScraperClient('YOUR_API_KEY');

// 1. Fetch live trending searches right now
$trends = $client->trendsNow(['gl' => 'US']);

echo "🔥 Top Trending Searches in US:\n";
foreach ($trends as $t) {
    echo "#{$t['position']} {$t['keyword']}\n";
}

// 2. Fetch Google Trends Interest Over Time
$interest = $client->trendsInterest('deepseek', [
    'gl' => 'US',
    'time' => 'today 12-m',
]);

echo "\n📊 Search Interest History:\n";
foreach ($interest->timelineData() as $point) {
    echo "{$point['date']}: {$point['value']}\n";
}
```

---

## 🔴 Laravel Integration

`tienphat/serpscraper-php` includes native Laravel auto-discovery.

### 1. Publish Configuration

```bash
php artisan vendor:publish --tag=serpscraper-config
```

### 2. Add API Key to `.env`

```env
SERPSCRAPER_API_KEY=your_secret_api_key_here
```

### 3. Use via Facade or Dependency Injection

```php
use SerpScraper\Laravel\Facades\SerpScraper;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', 'artificial intelligence');
        
        $results = SerpScraper::googleSearch($query, [
            'gl' => 'US',
            'hl' => 'en',
        ]);

        return response()->json([
            'results' => $results->data(),
            'ai_overview' => $results->aiOverview(),
            'summary' => $results->summary(),
        ]);
    }
}
```

---

## 🌐 Supported API Methods

All methods return a rich `SerpScraperResponse` object implementing `ArrayAccess`, `IteratorAggregate`, and `Countable`:

| Method | API Endpoint | Documentation & Landing Page |
|---|---|---|
| `$client->googleSearch($kw, $opts)` | `webs-search` (engine=google) | [Google Search API](https://serpscraper.dev/web-search-api) |
| `$client->webSearch($kw, $opts)` | `webs-search` | [Web Search API](https://serpscraper.dev/web-search-api) |
| `$client->aioSearch($kw, $opts)` | `aio` | [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api) |
| `$client->trendsNow($opts)` | `trends-now-search` | [Google Trends API (Real-Time)](https://serpscraper.dev/google-trends-api) |
| `$client->trendsInterest($kw, $opts)` | `trends-interest-search` | [Google Trends Interest Over Time](https://serpscraper.dev/google-trends-api) |
| `$client->imageSearch($kw, $opts)` | `images-search` | [Image Search API](https://serpscraper.dev/image-search-api) |
| `$client->videoSearch($kw, $opts)` | `videos-search` | [Video Search API](https://serpscraper.dev/video-search-api) |
| `$client->newsSearch($kw, $opts)` | `news-search` | [News Search API](https://serpscraper.dev/news-search-api) |
| `$client->shoppingSearch($kw, $opts)` | `shopping-search` | [Shopping Search API](https://serpscraper.dev/shopping-search-api) |
| `$client->scholarSearch($kw, $opts)` | `scholar-search` | [Google Scholar API](https://serpscraper.dev/scholar-search-api) |
| `$client->mapsSearch($kw, $opts)` | `maps-search` | [Google Maps API](https://serpscraper.dev/maps-search-api) |
| `$client->reviewsSearch($kw, $opts)` | `reviews-search` | [Reviews Search API](https://serpscraper.dev/reviews-search-api) |
| `$client->autocomplete($kw, $opts)` | `autocomplete` | [SERP Autocomplete Suggestions](https://serpscraper.dev/serp-api) |
| `$client->extractWebpage($url)` | `webpage` | [Webpage Content Extractor API](https://serpscraper.dev/webpage-api) |
| `$client->ipLookup($ip)` | `ip-lookup` | [IP Geolocation & Threat API](https://serpscraper.dev/ip-lookup-api) |
| `$client->exchangeRate($from, $to)` | `exchange-rate` | [Exchange Rate API](https://serpscraper.dev/exchange-rate-api) |
| `$client->cryptoPrice($symbols, $vs)` | `crypto-price` | [Cryptocurrency Price API](https://serpscraper.dev/crypto-price-api) |
| `$client->domainInfo($domain)` | `domain-info` | [Domain Intelligence & WHOIS API](https://serpscraper.dev/domain-intelligence-api) |

---

## 🛡️ Error Handling

The SDK provides granular exceptions inheriting from `SerpScraper\Exceptions\SerpScraperException`:

```php
use SerpScraper\Exceptions\SerpScraperException;
use SerpScraper\Exceptions\AuthenticationException;
use SerpScraper\Exceptions\RateLimitException;
use SerpScraper\Exceptions\NetworkException;

try {
    $results = $client->googleSearch('keyword');
} catch (AuthenticationException $e) {
    // Invalid or missing API key
    echo "Auth error: " . $e->getMessage();
} catch (RateLimitException $e) {
    // Credit limit or rate quota exceeded
    echo "Rate limit reached: " . $e->getMessage();
} catch (NetworkException $e) {
    // Network timeout or connectivity issue
    echo "Network error: " . $e->getMessage();
} catch (SerpScraperException $e) {
    // Generic API error
    echo "API error: " . $e->getMessage();
}
```

---

## 🧪 Testing

Run unit tests via PHPUnit:

```bash
vendor/bin/phpunit
```

---

## 📚 Resources & Documentation

- 🌐 **Website**: [https://serpscraper.dev](https://serpscraper.dev)
- 📖 **Official API Documentation**: [https://serpscraper.dev/docs](https://serpscraper.dev/docs)
- 🔑 **Get Free API Key**: [https://serpscraper.dev](https://serpscraper.dev)
- 🐛 **Issue Tracker**: [GitHub Issues](https://github.com/tienphat/serpscraper-php/issues)
- 💬 **Telegram Support**: [@peterpanpro](https://t.me/peterpanpro)

---

## 📄 License

The SerpScraper PHP SDK is open-sourced software licensed under the [MIT License](../LICENSE).

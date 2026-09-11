<?php

declare(strict_types=1);

namespace SerpScraper;

use SerpScraper\Contracts\SerpScraperClientInterface;
use SerpScraper\Exceptions\AuthException;
use SerpScraper\Exceptions\NetworkException;
use SerpScraper\Exceptions\RateLimitException;
use SerpScraper\Exceptions\SerpScraperException;

/**
 * Official PHP client for SerpScraper.Dev.
 *
 * Simple:  new SerpScraperClient('your-token')
 * Config:  new SerpScraperClient(SerpScraperConfig::make('token')->country('US'))
 * Laravel: new SerpScraperClient(SerpScraperConfig::fromArray(config('serpscraper')))
 *
 * @link https://serpscraper.dev/docs
 */
class SerpScraperClient implements SerpScraperClientInterface
{
    private SerpScraperConfig $config;

    /** @param SerpScraperConfig|string $config Token string or a SerpScraperConfig object. */
    public function __construct(SerpScraperConfig|string $config)
    {
        $this->config = is_string($config) ? SerpScraperConfig::make($config) : $config;
    }

    // ── Endpoints ────────────────────────────────────────────────────────────

    /** Google Web Search with organic rankings + Google AI Overview. */
    public function googleSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->webSearch($keyword, ['engine' => 'google'] + $options);
    }

    /** Web search results — titles, links, snippets. Supports engine => 'google' or default Bing. */
    public function webSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('webs-search', ['keyword' => $keyword] + $options);
    }

    /** AI Overview standalone synthesis from Google Grounding / Gemini or Bing AIO. */
    public function aioSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('aio', ['keyword' => $keyword] + $options);
    }

    /** Google Real-time Trending Searches by country. */
    public function trendsNow(array $options = []): SerpScraperResponse
    {
        return $this->call('trends-now-search', $options);
    }

    /** Google Trends Interest Over Time with timeframe and category filtering. */
    public function trendsInterest(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('trends-interest-search', ['keyword' => $keyword] + $options);
    }

    /** Image results with thumbnails and source URLs. */
    public function imageSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('images-search', ['keyword' => $keyword] + $options);
    }

    /** Video results — publisher, views, duration. */
    public function videoSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('videos-search', ['keyword' => $keyword] + $options);
    }

    /** News articles with source and publication date. */
    public function newsSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('news-search', ['keyword' => $keyword] + $options);
    }

    /** Shopping products — price, merchant, thumbnail. */
    public function shoppingSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('shopping-search', ['keyword' => $keyword] + $options);
    }

    /** Search suggestions / autocomplete. */
    public function autocomplete(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('autocomplete', ['keyword' => $keyword] + $options);
    }

    /** Google Scholar — citations, year, PDF links. */
    public function scholarSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('scholar-search', ['keyword' => $keyword] + $options);
    }

    /** Local/maps places — address, phone, hours, coordinates. */
    public function mapsSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('maps-search', ['keyword' => $keyword] + $options);
    }

    /** Review-focused results with rating signals. */
    public function reviewsSearch(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('reviews-search', ['keyword' => $keyword] + $options);
    }

    /** Unified endpoint — auto-detects result type or SerpApi format. */
    public function search(string $keyword, array $options = []): SerpScraperResponse
    {
        return $this->call('search', ['keyword' => $keyword] + $options);
    }

    /** Extract content, metadata, images and links from any URL. */
    public function extractWebpage(string $url, bool $includeHtml = false): SerpScraperResponse
    {
        $params = ['url' => $url];
        if ($includeHtml) {
            $params['include_html'] = 'true';
        }
        return $this->call('webpage', $params);
    }

    /** IP Geolocation & Proxy/Hosting/Mobile Detection. */
    public function ipLookup(?string $ip = null, array $options = []): SerpScraperResponse
    {
        $params = $options;
        if ($ip !== null && $ip !== '') {
            $params['ip'] = $ip;
        }
        return $this->call('ip-lookup', $params);
    }

    /** Fiat Currency Exchange Rates (ECB). */
    public function exchangeRate(?string $from = 'USD', ?string $to = null, array $options = []): SerpScraperResponse
    {
        $params = $options;
        if ($from !== null && $from !== '') {
            $params['from'] = $from;
        }
        if ($to !== null && $to !== '') {
            $params['to'] = $to;
        }
        return $this->call('exchange-rate', $params);
    }

    /** Real-time Crypto Market Prices via CoinGecko. */
    public function cryptoPrice(?string $symbols = 'btc,eth', ?string $vsCurrencies = 'usd', array $options = []): SerpScraperResponse
    {
        $params = $options;
        if ($symbols !== null && $symbols !== '') {
            $params['symbols'] = $symbols;
        }
        if ($vsCurrencies !== null && $vsCurrencies !== '') {
            $params['vs_currencies'] = $vsCurrencies;
        }
        return $this->call('crypto-price', $params);
    }

    /** Complete Domain Intelligence (WHOIS, SSL, DNS, Subdomains, Metadata). */
    public function domainInfo(string $domain, array $options = []): SerpScraperResponse
    {
        return $this->call('domain-info', ['domain' => $domain] + $options);
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function call(string $endpoint, array $params): SerpScraperResponse
    {
        if ($this->config->getCountry() && !isset($params['gl'])) {
            $params['gl'] = $this->config->getCountry();
        }
        if ($this->config->getLanguage() && !isset($params['hl'])) {
            $params['hl'] = $this->config->getLanguage();
        }

        $params['token'] = $this->config->getToken();
        $url = $this->config->getBaseUrl() . '/' . ltrim($endpoint, '/') . '?' . http_build_query($params);

        [$raw, $httpCode] = $this->send($url);

        return $this->parseResponse($raw, $httpCode);
    }

    /** @return array{0: string, 1: int} */
    protected function send(string $url): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => $this->config->getTimeout(),
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'User-Agent: ' . $this->config->getUserAgent(),
            ],
        ]);

        $body  = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($errno || $body === false) {
            throw new NetworkException("Network error: {$error} (errno {$errno})");
        }

        return [(string) $body, $code];
    }

    /** @throws SerpScraperException */
    protected function parseResponse(string $body, int $httpCode): SerpScraperResponse
    {
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SerpScraperException("Invalid JSON from API (HTTP {$httpCode}): " . json_last_error_msg());
        }

        $message = (string) ($data['message'] ?? $data['error'] ?? "HTTP {$httpCode}");

        match (true) {
            $httpCode === 401,
            $httpCode === 403 => throw new AuthException($message, $httpCode),
            $httpCode === 429 => throw new RateLimitException($message, $httpCode),
            $httpCode >= 400  => throw new SerpScraperException($message, $httpCode),
            default           => null,
        };

        return new SerpScraperResponse($data);
    }
}

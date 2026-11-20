<?php

declare(strict_types=1);

namespace SerpScraper\Contracts;

use SerpScraper\SerpScraperResponse;

/**
 * Contract for SerpScraperClient — useful for mocking and dependency injection.
 */
interface SerpScraperClientInterface
{
    public function googleSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function webSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function aioSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function trendsNow(array $options = []): SerpScraperResponse;
    public function trendsInterest(string $keyword, array $options = []): SerpScraperResponse;
    public function imageSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function videoSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function newsSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function shoppingSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function autocomplete(string $keyword, array $options = []): SerpScraperResponse;
    public function scholarSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function mapsSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function reviewsSearch(string $keyword, array $options = []): SerpScraperResponse;
    public function search(string $keyword, array $options = []): SerpScraperResponse;
    public function extractWebpage(string $url, bool $includeHtml = false): SerpScraperResponse;
    public function ipLookup(?string $ip = null, array $options = []): SerpScraperResponse;
    public function exchangeRate(?string $from = 'USD', ?string $to = null, array $options = []): SerpScraperResponse;
    public function cryptoPrice(?string $symbols = 'btc,eth', ?string $vsCurrencies = 'usd', array $options = []): SerpScraperResponse;
    public function domainInfo(string $domain, array $options = []): SerpScraperResponse;
    public function seoDomainOverview(string $domain, array $options = []): SerpScraperResponse;
    public function seoBacklinks(string $domain, array $options = []): SerpScraperResponse;
    public function seoKeywordMetrics(string|array $keywords, array $options = []): SerpScraperResponse;
    public function seoKeywordSuggestions(string $keyword, array $options = []): SerpScraperResponse;
    public function proxySession(?string $country = null, array $options = []): SerpScraperResponse;
    public function proxyRotate(string $sessionId, array $options = []): SerpScraperResponse;
    public function proxyLocations(array $options = []): SerpScraperResponse;
}

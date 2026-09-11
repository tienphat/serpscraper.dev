<?php

declare(strict_types=1);

namespace SerpScraper\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SerpScraper\Contracts\SerpScraperClientInterface;
use SerpScraper\Exceptions\AuthException;
use SerpScraper\Exceptions\NetworkException;
use SerpScraper\Exceptions\RateLimitException;
use SerpScraper\Exceptions\SerpScraperException;
use SerpScraper\SerpScraperClient;
use SerpScraper\SerpScraperConfig;
use SerpScraper\SerpScraperResponse;

/** HTTP calls are intercepted by a CapturingClient/stub subclass — no network required. */
#[CoversClass(SerpScraperClient::class)]
class SerpScraperClientTest extends TestCase
{
    // ── Constructor ───────────────────────────────────────────────────────────

    public function test_accepts_token_string(): void
    {
        $client = new SerpScraperClient('my-token');
        $this->assertInstanceOf(SerpScraperClient::class, $client);
    }

    public function test_accepts_config_object(): void
    {
        $config = SerpScraperConfig::make('cfg-token');
        $client = new SerpScraperClient($config);
        $this->assertInstanceOf(SerpScraperClient::class, $client);
    }

    public function test_throws_on_empty_token_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SerpScraperClient('');
    }

    public function test_implements_interface(): void
    {
        $client = new SerpScraperClient('tok');
        $this->assertInstanceOf(SerpScraperClientInterface::class, $client);
    }

    // ── Successful responses ──────────────────────────────────────────────────

    public function test_web_search_returns_response(): void
    {
        $client = $this->stubClient(200, $this->fakeWebPayload());
        $res    = $client->webSearch('laravel');

        $this->assertInstanceOf(SerpScraperResponse::class, $res);
        $this->assertSame(2, $res->count());
        $this->assertSame('Laravel - The PHP Framework', $res->first()['title']);
    }

    public function test_image_search_returns_response(): void
    {
        $client = $this->stubClient(200, $this->fakeImagePayload());
        $res    = $client->imageSearch('php logo');

        $this->assertFalse($res->isEmpty());
        $this->assertArrayHasKey('image_url', $res->first());
    }

    public function test_news_search_returns_response(): void
    {
        $client = $this->stubClient(200, $this->fakeNewsPayload());
        $res    = $client->newsSearch('ai news');

        $this->assertSame(2, $res->count());
        $this->assertArrayHasKey('source', $res->first());
    }

    public function test_extract_webpage_returns_response(): void
    {
        $client = $this->stubClient(200, $this->fakeWebpagePayload());
        $res    = $client->extractWebpage('https://example.com');

        $this->assertSame('Example Domain', $res->toArray()['data']['title']);
    }

    public function test_response_exposes_request_params(): void
    {
        $client = $this->stubClient(200, [
            'request'    => ['keyword' => 'test', 'gl' => 'US', 'token' => 'xxx'],
            'data'       => [],
            'in_seconds' => 0.1,
        ]);

        $res = $client->webSearch('test');
        $this->assertSame('test', $res->request()['keyword']);
    }

    public function test_response_exposes_latency(): void
    {
        $client = $this->stubClient(200, $this->fakeWebPayload());
        $this->assertSame(0.35, $client->webSearch('x')->inSeconds());
    }

    public function test_foreach_iterates_results(): void
    {
        $client = $this->stubClient(200, $this->fakeWebPayload());
        $titles = [];

        foreach ($client->webSearch('x') as $item) {
            $titles[] = $item['title'];
        }

        $this->assertCount(2, $titles);
    }

    public function test_pluck_extracts_links(): void
    {
        $client = $this->stubClient(200, $this->fakeWebPayload());
        $links  = $client->webSearch('x')->pluck('link');

        $this->assertSame(['https://laravel.com', 'https://laravel.com/docs'], $links);
    }

    // ── Exception mapping ─────────────────────────────────────────────────────

    public function test_throws_auth_exception_on_401(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);

        $this->stubClient(401, ['message' => 'Invalid token'])->webSearch('x');
    }

    public function test_throws_auth_exception_on_403(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionCode(403);

        $this->stubClient(403, ['message' => 'Forbidden'])->webSearch('x');
    }

    public function test_throws_rate_limit_exception_on_429(): void
    {
        $this->expectException(RateLimitException::class);
        $this->expectExceptionCode(429);

        $this->stubClient(429, ['message' => 'Quota exceeded'])->webSearch('x');
    }

    public function test_throws_serpscraper_exception_on_500(): void
    {
        $this->expectException(SerpScraperException::class);
        $this->expectExceptionCode(500);

        $this->stubClient(500, ['message' => 'Server error'])->webSearch('x');
    }

    public function test_throws_serpscraper_exception_on_malformed_json(): void
    {
        $this->expectException(SerpScraperException::class);
        $this->expectExceptionMessageMatches('/Invalid JSON/');

        $this->stubClientRaw(200, 'this-is-not-json')->webSearch('x');
    }

    public function test_throws_network_exception_on_curl_failure(): void
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessageMatches('/Network error/');

        $this->stubClientNetworkError()->webSearch('x');
    }

    // ── URL construction & query params ───────────────────────────────────────

    public function test_default_country_is_injected(): void
    {
        $config = SerpScraperConfig::make('tok')->country('VN');
        $client = $this->stubClientCapture($config);

        $client->webSearch('test');

        $this->assertStringContainsString('gl=VN', $client->lastUrl());
    }

    public function test_per_call_country_overrides_default(): void
    {
        $config = SerpScraperConfig::make('tok')->country('US');
        $client = $this->stubClientCapture($config);

        $client->webSearch('test', ['gl' => 'GB']);

        $this->assertStringContainsString('gl=GB', $client->lastUrl());
        $this->assertStringNotContainsString('gl=US', $client->lastUrl());
    }

    public function test_default_language_is_injected(): void
    {
        $config = SerpScraperConfig::make('tok')->language('vi');
        $client = $this->stubClientCapture($config);

        $client->webSearch('test');

        $this->assertStringContainsString('hl=vi', $client->lastUrl());
    }

    public function test_token_is_always_appended_to_url(): void
    {
        $client = $this->stubClientCapture(SerpScraperConfig::make('secret-key'));
        $client->webSearch('test');

        $this->assertStringContainsString('token=secret-key', $client->lastUrl());
    }

    public function test_google_search_calls_webs_search_with_engine_google(): void
    {
        $client = $this->stubClientCapture(SerpScraperConfig::make('tok'));
        $client->googleSearch('openai');

        $this->assertStringContainsString('/webs-search?', $client->lastUrl());
        $this->assertStringContainsString('keyword=openai', $client->lastUrl());
        $this->assertStringContainsString('engine=google', $client->lastUrl());
    }

    public function test_aio_search_calls_aio_endpoint(): void
    {
        $client = $this->stubClientCapture(SerpScraperConfig::make('tok'));
        $client->aioSearch('artificial intelligence');

        $this->assertStringContainsString('/aio?', $client->lastUrl());
        $this->assertStringContainsString('keyword=artificial+intelligence', $client->lastUrl());
    }

    public function test_trends_now_calls_trends_now_search(): void
    {
        $client = $this->stubClientCapture(SerpScraperConfig::make('tok'));
        $client->trendsNow(['gl' => 'VN']);

        $this->assertStringContainsString('/trends-now-search?', $client->lastUrl());
        $this->assertStringContainsString('gl=VN', $client->lastUrl());
    }

    public function test_trends_interest_calls_trends_interest_search(): void
    {
        $client = $this->stubClientCapture(SerpScraperConfig::make('tok'));
        $client->trendsInterest('bitcoin', ['time' => 'today 12-m']);

        $this->assertStringContainsString('/trends-interest-search?', $client->lastUrl());
        $this->assertStringContainsString('keyword=bitcoin', $client->lastUrl());
        $this->assertStringContainsString('time=today+12-m', $client->lastUrl());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function stubClient(int $httpCode, array $body): SerpScraperClient
    {
        return new class(SerpScraperConfig::make('test-token'), $httpCode, json_encode($body)) extends SerpScraperClient {
            public function __construct(
                SerpScraperConfig $config,
                private int $code,
                private string $body,
            ) {
                parent::__construct($config);
            }

            protected function send(string $url): array { return [$this->body, $this->code]; }
        };
    }

    private function stubClientRaw(int $httpCode, string $rawBody): SerpScraperClient
    {
        return new class(SerpScraperConfig::make('test-token'), $httpCode, $rawBody) extends SerpScraperClient {
            public function __construct(
                SerpScraperConfig $config,
                private int $code,
                private string $body,
            ) {
                parent::__construct($config);
            }

            protected function send(string $url): array { return [$this->body, $this->code]; }
        };
    }

    private function stubClientNetworkError(): SerpScraperClient
    {
        return new class(SerpScraperConfig::make('test-token')) extends SerpScraperClient {
            protected function send(string $url): array
            {
                throw new NetworkException('Network error: Could not resolve host (errno 6)');
            }
        };
    }

    private function stubClientCapture(SerpScraperConfig $config): CapturingClient
    {
        return new CapturingClient($config);
    }

    private function fakeWebPayload(): array
    {
        return [
            'request'    => ['keyword' => 'laravel', 'gl' => 'US', 'hl' => 'en'],
            'data'       => [
                ['position' => 1, 'title' => 'Laravel - The PHP Framework', 'link' => 'https://laravel.com'],
                ['position' => 2, 'title' => 'Documentation - Laravel',     'link' => 'https://laravel.com/docs'],
            ],
            'in_seconds' => 0.35,
        ];
    }

    private function fakeImagePayload(): array
    {
        return [
            'data'       => [['title' => 'PHP logo', 'image_url' => 'https://example.com/logo.png']],
            'in_seconds' => 0.22,
        ];
    }

    private function fakeNewsPayload(): array
    {
        return [
            'data'       => [
                ['title' => 'AI update', 'source' => 'TechNews', 'date' => '1 hour ago'],
                ['title' => 'PHP 8.4',   'source' => 'DevBlog',  'date' => '2 days ago'],
            ],
            'in_seconds' => 0.18,
        ];
    }

    private function fakeWebpagePayload(): array
    {
        return [
            'data'       => ['title' => 'Example Domain', 'content' => 'This domain is for use in examples.'],
            'in_seconds' => 0.28,
        ];
    }
}

// ── Test helpers (defined outside the test class) ────────────────────────────

/**
 * Named subclass that captures the URL built by SerpScraperClient::call()
 * so URL-construction tests can assert on it without hitting the network.
 */
final class CapturingClient extends SerpScraperClient
{
    private string $capturedUrl = '';

    public function __construct(SerpScraperConfig $config)
    {
        parent::__construct($config);
    }

    protected function send(string $url): array
    {
        $this->capturedUrl = $url;
        return [json_encode(['data' => [], 'in_seconds' => 0.0, 'request' => []]), 200];
    }

    public function lastUrl(): string
    {
        return $this->capturedUrl;
    }
}

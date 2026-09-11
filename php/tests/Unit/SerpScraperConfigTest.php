<?php

declare(strict_types=1);

namespace SerpScraper\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SerpScraper\SerpScraperConfig;

#[CoversClass(SerpScraperConfig::class)]
class SerpScraperConfigTest extends TestCase
{
    // ── Factory: make() ───────────────────────────────────────────────────────

    public function test_make_sets_token(): void
    {
        $config = SerpScraperConfig::make('tok-123');
        $this->assertInstanceOf(SerpScraperConfig::class, $config);
        $this->assertSame('tok-123', $config->getToken());
    }

    public function test_make_trims_whitespace_check(): void
    {
        $config = SerpScraperConfig::make('my-secret-token');
        $this->assertSame('my-secret-token', $config->getToken());
    }

    public function test_make_throws_on_empty_token(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SerpScraper token must not be empty.');

        SerpScraperConfig::make('');
    }

    public function test_make_throws_on_whitespace_only_token(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SerpScraper token must not be empty.');

        SerpScraperConfig::make('   ');
    }

    // ── Defaults ─────────────────────────────────────────────────────────────

    public function test_defaults_are_sensible(): void
    {
        $config = SerpScraperConfig::make('tok');
        $this->assertSame(30, $config->getTimeout());
        $this->assertStringContainsString('serpscraper.dev/api/v1', $config->getBaseUrl());
        $this->assertNull($config->getCountry());
        $this->assertNull($config->getLanguage());
        $this->assertNotEmpty($config->getUserAgent());
    }

    // ── Fluent setters ───────────────────────────────────────────────────────

    public function test_country_returns_new_immutable_instance(): void
    {
        $original = SerpScraperConfig::make('tok');
        $modified = $original->country('VN');

        $this->assertNotSame($original, $modified);
        $this->assertNull($original->getCountry());
        $this->assertSame('VN', $modified->getCountry());
    }

    public function test_country_uppercases_value(): void
    {
        $config = SerpScraperConfig::make('tok')->country('gb');
        $this->assertSame('GB', $config->getCountry());
    }

    public function test_language_returns_new_immutable_instance(): void
    {
        $original = SerpScraperConfig::make('tok');
        $modified = $original->language('vi');

        $this->assertNotSame($original, $modified);
        $this->assertNull($original->getLanguage());
        $this->assertSame('vi', $modified->getLanguage());
    }

    public function test_language_lowercases_value(): void
    {
        $config = SerpScraperConfig::make('tok')->language('EN');
        $this->assertSame('en', $config->getLanguage());
    }

    public function test_timeout_returns_new_immutable_instance(): void
    {
        $config = SerpScraperConfig::make('tok')->timeout(15);
        $this->assertSame(15, $config->getTimeout());
    }

    public function test_timeout_throws_on_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SerpScraperConfig::make('tok')->timeout(0);
    }

    public function test_timeout_throws_on_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SerpScraperConfig::make('tok')->timeout(-5);
    }

    public function test_base_url_strips_trailing_slash(): void
    {
        $config = SerpScraperConfig::make('tok')->baseUrl('https://example.com/api/v1/');
        $this->assertSame('https://example.com/api/v1', $config->getBaseUrl());
    }

    public function test_fluent_chaining_works(): void
    {
        $config = SerpScraperConfig::make('abc')
            ->country('US')
            ->language('en')
            ->timeout(10)
            ->baseUrl('https://custom.api/v1');

        $this->assertSame('abc', $config->getToken());
        $this->assertSame('US', $config->getCountry());
        $this->assertSame('en', $config->getLanguage());
        $this->assertSame(10, $config->getTimeout());
        $this->assertSame('https://custom.api/v1', $config->getBaseUrl());
    }

    // ── Factory: fromArray() ──────────────────────────────────────────────────

    public function test_from_array_populates_all_fields(): void
    {
        $config = SerpScraperConfig::fromArray([
            'token'      => 'arr-tok',
            'base_url'   => 'https://serpscraper.dev/api/v1',
            'timeout'    => 45,
            'country'    => 'DE',
            'language'   => 'de',
            'user_agent' => 'CustomBot/1.0',
        ]);

        $this->assertSame('arr-tok', $config->getToken());
        $this->assertSame('https://serpscraper.dev/api/v1', $config->getBaseUrl());
        $this->assertSame(45, $config->getTimeout());
        $this->assertSame('DE', $config->getCountry());
        $this->assertSame('de', $config->getLanguage());
        $this->assertSame('CustomBot/1.0', $config->getUserAgent());
    }

    public function test_from_array_throws_on_missing_token(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SerpScraperConfig::fromArray(['country' => 'US']);
    }

    public function test_from_array_accepts_minimal_array(): void
    {
        $config = SerpScraperConfig::fromArray(['token' => 'min-token']);
        $this->assertSame('min-token', $config->getToken());
        $this->assertSame(30, $config->getTimeout());
    }
}

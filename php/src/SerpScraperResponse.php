<?php

declare(strict_types=1);

namespace SerpScraper;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Wraps the raw SerpScraper API response with convenient accessors.
 *
 *   $res->data()         → list of results
 *   $res->first()        → first result or null
 *   $res->pluck('link')  → array of a single field
 *   $res->aiOverview()   → full AI overview payload if present
 *   $res->summary()      → summary text of AI overview
 *   $res->sources()      → citations/sources of AI overview
 *   $res->timelineData() → timeline points for Google Trends
 *   $res->count()        → number of results
 *   $res->inSeconds()    → API latency
 *   $res->toArray()      → full raw response
 *   foreach ($res …)     → iterate results directly
 *
 * @implements Countable
 * @implements IteratorAggregate<int, array>
 */
final class SerpScraperResponse implements Countable, IteratorAggregate
{
    /** @param array<string, mixed> $raw */
    public function __construct(private readonly array $raw) {}

    // ── Data ─────────────────────────────────────────────────────────────────

    /** @return list<array<string, mixed>> */
    public function data(): array
    {
        $data = $this->raw['data'] ?? [];

        // Some endpoints wrap results under a typed key
        if (is_array($data) && !array_is_list($data)) {
            foreach (['items', 'products', 'images', 'videos', 'news', 'scholar', 'places', 'reviews', 'suggestions', 'interest_over_time', 'trending_searches'] as $key) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    return $data[$key];
                }
            }
        }

        return is_array($data) ? $data : [];
    }

    // ── AI Overview & Trends ──────────────────────────────────────────────────

    /** Google / Bing AI Overview data structure if present. */
    public function aiOverview(): ?array
    {
        return $this->raw['ai_overview'] ?? null;
    }

    /** Quick summary text from AI Overview. */
    public function summary(): ?string
    {
        return $this->raw['ai_overview']['summary'] ?? null;
    }

    /** Sources/references cited in AI Overview. */
    public function sources(): array
    {
        return $this->raw['ai_overview']['sources'] ?? [];
    }

    /** Timeline data points from Google Trends interest over time. */
    public function timelineData(): array
    {
        $data = $this->raw['data'] ?? [];
        if (isset($data['interest_over_time']) && is_array($data['interest_over_time'])) {
            return $data['interest_over_time'];
        }
        if (isset($data['timeline_data']) && is_array($data['timeline_data'])) {
            return $data['timeline_data'];
        }
        return [];
    }

    /** First result, or null when empty. */
    public function first(): ?array
    {
        return $this->data()[0] ?? null;
    }

    /**
     * Pluck a single field from every result item.
     *
     * @return list<mixed>
     */
    public function pluck(string $field): array
    {
        return array_values(
            array_filter(array_column($this->data(), $field), static fn ($v) => $v !== null)
        );
    }

    // ── Meta ─────────────────────────────────────────────────────────────────

    /** Echo of the parameters sent to the API. */
    public function request(): array
    {
        return (array) ($this->raw['request'] ?? []);
    }

    /** Pagination info (total_results, page) when present. */
    public function meta(): array
    {
        return array_filter([
            'total_results' => $this->raw['data']['total_results'] ?? null,
            'page'          => $this->raw['data']['page'] ?? null,
        ], static fn ($v) => $v !== null);
    }

    /** API processing time in seconds. */
    public function inSeconds(): float
    {
        return (float) ($this->raw['in_seconds'] ?? 0.0);
    }

    /** True when the response is a static sample rather than a live call. */
    public function isStaticSample(): bool
    {
        return (bool) ($this->raw['static_sample'] ?? false);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    // ── Serialise ────────────────────────────────────────────────────────────

    /** Full raw response. */
    public function toArray(): array
    {
        return $this->raw;
    }

    public function toJson(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string
    {
        return (string) json_encode($this->raw, $flags);
    }

    // ── Interfaces ───────────────────────────────────────────────────────────

    public function count(): int
    {
        return count($this->data());
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data());
    }
}

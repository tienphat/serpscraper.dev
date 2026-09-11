<?php

declare(strict_types=1);

namespace SerpScraper\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SerpScraper\SerpScraperResponse;

#[CoversClass(SerpScraperResponse::class)]
class SerpScraperResponseTest extends TestCase
{
    private function makeResponse(array $raw = []): SerpScraperResponse
    {
        return new SerpScraperResponse($raw);
    }

    private function makeWebResponse(int $count = 3): SerpScraperResponse
    {
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $items[] = [
                'position'    => $i,
                'title'       => "Result {$i}",
                'link'        => "https://example.com/{$i}",
                'description' => "Snippet for result {$i}",
            ];
        }

        return $this->makeResponse([
            'request'    => ['keyword' => 'test', 'gl' => 'US'],
            'data'       => $items,
            'in_seconds' => 0.42,
        ]);
    }

    // ── data() ────────────────────────────────────────────────────────────────

    public function test_data_returns_list(): void
    {
        $res = $this->makeWebResponse(3);
        $this->assertCount(3, $res->data());
    }

    public function test_data_returns_empty_array_when_missing(): void
    {
        $res = $this->makeResponse([]);
        $this->assertSame([], $res->data());
    }

    public function test_data_returns_empty_array_when_data_is_not_array(): void
    {
        $res = $this->makeResponse(['data' => 'not-an-array']);
        $this->assertSame([], $res->data());
    }

    // ── first() ───────────────────────────────────────────────────────────────

    public function test_first_returns_first_item(): void
    {
        $res = $this->makeWebResponse(3);
        $first = $res->first();

        $this->assertNotNull($first);
        $this->assertSame('Result 1', $first['title']);
    }

    public function test_first_returns_null_when_empty(): void
    {
        $res = $this->makeResponse([]);
        $this->assertNull($res->first());
    }

    // ── pluck() ───────────────────────────────────────────────────────────────

    public function test_pluck_extracts_column(): void
    {
        $res = $this->makeWebResponse(3);
        $titles = $res->pluck('title');

        $this->assertSame(['Result 1', 'Result 2', 'Result 3'], $titles);
    }

    public function test_pluck_ignores_missing_keys(): void
    {
        $res = $this->makeResponse([
            'data' => [
                ['title' => 'A', 'extra' => 'yes'],
                ['title' => 'B'],
            ],
        ]);

        $this->assertSame(['yes'], $res->pluck('extra'));
    }

    public function test_pluck_returns_empty_when_no_data(): void
    {
        $res = $this->makeResponse([]);
        $this->assertSame([], $res->pluck('link'));
    }

    // ── meta() ────────────────────────────────────────────────────────────────

    public function test_meta_returns_pagination_info(): void
    {
        $res = $this->makeResponse([
            'data' => [
                'total_results' => 1250,
                'page'          => 2,
            ],
        ]);

        $this->assertSame(['total_results' => 1250, 'page' => 2], $res->meta());
    }

    public function test_meta_returns_empty_when_no_pagination(): void
    {
        $res = $this->makeWebResponse(2);
        $this->assertSame([], $res->meta());
    }

    // ── inSeconds() ───────────────────────────────────────────────────────────

    public function test_in_seconds_returns_float(): void
    {
        $res = $this->makeWebResponse(1);
        $this->assertSame(0.42, $res->inSeconds());
    }

    public function test_in_seconds_defaults_to_zero(): void
    {
        $res = $this->makeResponse([]);
        $this->assertSame(0.0, $res->inSeconds());
    }

    // ── isStaticSample() ──────────────────────────────────────────────────────

    public function test_is_static_sample_returns_true_when_flagged(): void
    {
        $res = $this->makeResponse(['static_sample' => true]);
        $this->assertTrue($res->isStaticSample());
    }

    public function test_is_static_sample_defaults_to_false(): void
    {
        $res = $this->makeResponse([]);
        $this->assertFalse($res->isStaticSample());
    }

    // ── isEmpty() ─────────────────────────────────────────────────────────────

    public function test_is_empty_returns_true_when_no_results(): void
    {
        $res = $this->makeResponse([]);
        $this->assertTrue($res->isEmpty());
    }

    public function test_is_empty_returns_false_when_results_exist(): void
    {
        $res = $this->makeWebResponse(1);
        $this->assertFalse($res->isEmpty());
    }

    // ── Countable ─────────────────────────────────────────────────────────────

    public function test_count_matches_results(): void
    {
        $res = $this->makeWebResponse(5);
        $this->assertCount(5, $res);
        $this->assertSame(5, $res->count());
    }

    // ── IteratorAggregate ─────────────────────────────────────────────────────

    public function test_iterable_with_foreach(): void
    {
        $res = $this->makeWebResponse(3);
        $collected = [];

        foreach ($res as $i => $item) {
            $collected[$i] = $item['title'];
        }

        $this->assertSame(['Result 1', 'Result 2', 'Result 3'], $collected);
    }

    // ── toArray() & toJson() ──────────────────────────────────────────────────

    public function test_to_array_returns_full_raw(): void
    {
        $raw = ['data' => [['title' => 'T1']], 'in_seconds' => 0.1];
        $res = $this->makeResponse($raw);
        $this->assertSame($raw, $res->toArray());
    }

    public function test_to_json_is_valid_json(): void
    {
        $res  = $this->makeWebResponse(2);
        $json = $res->toJson();
        $this->assertJson($json);
        $this->assertArrayHasKey('data', json_decode($json, true));
    }

    // ── Nested data format (e.g. unified endpoint) ────────────────────────────

    public function test_data_extracts_nested_items_key(): void
    {
        $res = $this->makeResponse([
            'data' => ['items' => [['id' => 1], ['id' => 2]]],
        ]);

        $this->assertCount(2, $res->data());
        $this->assertSame(1, $res->data()[0]['id']);
    }

    public function test_data_extracts_nested_news_key(): void
    {
        $res = $this->makeResponse([
            'data' => ['news' => [['title' => 'N1'], ['title' => 'N2']]],
        ]);

        $this->assertSame('N1', $res->data()[0]['title']);
    }

    // ── AI Overview & Trends Accessors ────────────────────────────────────────

    public function test_ai_overview_accessors(): void
    {
        $payload = [
            'data' => [],
            'ai_overview' => [
                'summary' => 'OpenAI is an AI research company.',
                'summary_html' => '<p>OpenAI is an AI research company.</p>',
                'sources' => [
                    ['title' => 'Wikipedia', 'link' => 'https://en.wikipedia.org/wiki/OpenAI']
                ],
            ],
        ];

        $res = $this->makeResponse($payload);
        $this->assertNotNull($res->aiOverview());
        $this->assertSame('OpenAI is an AI research company.', $res->summary());
        $this->assertCount(1, $res->sources());
        $this->assertSame('Wikipedia', $res->sources()[0]['title']);
    }

    public function test_timeline_data_accessor(): void
    {
        $payload = [
            'data' => [
                'interest_over_time' => [
                    ['date' => 'Sep 2026', 'value' => 85]
                ],
            ],
        ];

        $res = $this->makeResponse($payload);
        $this->assertCount(1, $res->timelineData());
        $this->assertSame(85, $res->timelineData()[0]['value']);
        $this->assertSame(85, $res->data()[0]['value']);
    }
}

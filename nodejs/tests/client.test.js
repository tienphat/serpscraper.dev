const test = require('node:test');
const assert = require('node:assert');
const { SerpScraperClient, SerpScraperResponse } = require('../src/index');

test('SerpScraperClient validates token', () => {
  assert.throws(() => {
    new SerpScraperClient('');
  }, /token must not be empty/);

  const client = new SerpScraperClient('my-token');
  assert.strictEqual(client.token, 'my-token');
  assert.strictEqual(client.baseUrl, 'https://serpscraper.dev/api/v1');
});

test('SEO and proxy endpoints build expected URLs', async () => {
  const urls = [];
  const realFetch = global.fetch;
  global.fetch = async (url) => {
    urls.push(String(url));
    return { ok: true, status: 200, text: async () => JSON.stringify({ data: [], in_seconds: 0 }), json: async () => ({ data: [], in_seconds: 0 }) };
  };

  try {
    const client = new SerpScraperClient('tok');
    await client.seoDomainOverview('apple.com');
    await client.seoBacklinks('github.com');
    await client.seoKeywordMetrics(['serp api', 'rank tracker']);
    await client.seoKeywordSuggestions('laravel');
    await client.proxySession('US');
    await client.proxySession();
    await client.proxyRotate('abc123');
    await client.proxyLocations();
  } finally {
    global.fetch = realFetch;
  }

  assert.match(urls[0], /\/seo\/domain-overview\?.*domain=apple\.com/);
  assert.match(urls[1], /\/seo\/backlinks\?/);
  assert.match(urls[2], /\/seo\/keywords\/metrics\?.*keywords=serp\+api%2Crank\+tracker/);
  assert.match(urls[3], /\/seo\/keywords\/suggestions\?/);
  assert.match(urls[4], /\/proxy\/session\?.*country=US/);
  assert.doesNotMatch(urls[5], /country=/);
  assert.match(urls[6], /session_id=abc123/);
  assert.match(urls[7], /\/proxy\/locations\?.*token=tok/);
});

test('SerpScraperResponse handles data accessors', () => {
  const raw = {
    data: [
      { position: 1, title: 'Item 1', link: 'https://item1.com' },
      { position: 2, title: 'Item 2', link: 'https://item2.com' },
    ],
    ai_overview: {
      summary: 'Short AI summary',
      sources: [{ title: 'Source A', link: 'https://a.com' }],
    },
    in_seconds: 0.12,
  };

  const res = new SerpScraperResponse(raw);
  assert.strictEqual(res.length, 2);
  assert.strictEqual(res.first.title, 'Item 1');
  assert.deepStrictEqual(res.pluck('link'), ['https://item1.com', 'https://item2.com']);
  assert.strictEqual(res.summary, 'Short AI summary');
  assert.strictEqual(res.sources.length, 1);
  assert.strictEqual(res.inSeconds, 0.12);
  assert.strictEqual(res.isEmpty, false);
});

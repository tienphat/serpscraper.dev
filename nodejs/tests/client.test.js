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

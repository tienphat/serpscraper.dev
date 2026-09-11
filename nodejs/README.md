# serpscraper: Official Node.js & TypeScript SDK for SerpScraper.Dev (Google SERP API, AI Overview & Trends)

<p align="center">
  <a href="https://serpscraper.dev"><img src="https://serpscraper.dev/img/logo.png" width="100" alt="SerpScraper.Dev Logo"></a>
</p>

<p align="center">
  Official <strong>Node.js & TypeScript</strong> client library for <a href="https://serpscraper.dev"><strong>SerpScraper.Dev</strong></a> — The fast, unblockable <a href="https://serpscraper.dev/serp-api">SERP API</a> for real-time Google search rankings, <a href="https://serpscraper.dev/google-ai-overview-api">Google AI Overviews</a>, and <a href="https://serpscraper.dev/google-trends-api">Google Trends</a>.
</p>

<p align="center">
  <a href="https://www.npmjs.com/package/serpscraper"><img src="https://img.shields.io/npm/v/serpscraper.svg" alt="npm version"></a>
  <img src="https://img.shields.io/badge/Node.js-18%2B-339933?logo=node.js" alt="Node.js 18+">
  <img src="https://img.shields.io/badge/TypeScript-Ready-3178C6?logo=typescript" alt="TypeScript Ready">
  <img src="https://img.shields.io/badge/Dependencies-Zero-success" alt="Zero Dependencies">
  <a href="../LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="MIT License"></a>
  <a href="https://serpscraper.dev"><img src="https://img.shields.io/badge/API%20Key-Free%20Tier-green" alt="Free API Key"></a>
</p>

---

## ⚡ Why Use `serpscraper` for JavaScript & TypeScript?

- 🟩 **Zero External Dependencies**: Implemented strictly with native Node.js `fetch` (Node.js 18+). No axios, no node-fetch, no vulnerability bloat.
- 🟦 **Full TypeScript Support**: Shipped with comprehensive `.d.ts` type declarations for autocompletion on every parameter and response attribute.
- 🤖 **Native Google AI Overview Extraction**: Parse synthesized AI answers, citation cards, and markdown text effortlessly with `res.summary` and `res.sources` via the [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api).
- 📈 **Real-Time Google Trends**: Monitor breaking news, trending searches, and historical search volume timelines using the [Google Trends API](https://serpscraper.dev/google-trends-api).
- ⚡ **Edge & Serverless Ready**: Works seamlessly in Next.js (App Router & Pages Router), Remix, Cloudflare Workers, Express, Fastify, and NestJS.
- 🛡️ **Zero Block Guarantee**: Built-in rotating residential proxies by [SerpScraper.Dev](https://serpscraper.dev) bypass Cloudflare and Google CAPTCHAs.

---

## 📦 Installation

Install via npm, yarn, or pnpm:

```bash
npm install serpscraper
# or
yarn add serpscraper
# or
pnpm add serpscraper
```

---

## 🚀 Quick Start

Get your free API key at [serpscraper.dev](https://serpscraper.dev).

### 1. Google Web Search & AI Overview Extraction

#### CommonJS (Node.js)
```javascript
const { SerpScraperClient } = require('serpscraper');

const client = new SerpScraperClient('YOUR_API_KEY');

async function main() {
  // Scrape Google organic results + AI Overview targeting US English
  const res = await client.googleSearch('best ai code editors 2025', {
    gl: 'US',
    hl: 'en',
    limit: 10,
  });

  console.log(`Fetched ${res.length} organic results in ${res.inSeconds}s:\n`);

  // 1. Iterate through Organic Search Rankings
  for (const item of res) {
    console.log(`#${item.position} ${item.title}`);
    console.log(`    URL: ${item.link}`);
    console.log(`    Snippet: ${item.snippet}\n`);
  }

  // 2. Extract Google AI Overview (SGE) & Sources
  if (res.summary) {
    console.log('──────────────────────────────────────────');
    console.log('🤖 [Google AI Overview]:\n', res.summary);
    console.log('──────────────────────────────────────────');
    console.log('Cited Sources:');
    for (const src of res.sources) {
      console.log(` - ${src.title} (${src.link})`);
    }
  }
}

main();
```

#### TypeScript / ESM
```typescript
import { SerpScraperClient, SerpScraperResponse } from 'serpscraper';

const client = new SerpScraperClient('YOUR_API_KEY');

const res: SerpScraperResponse = await client.googleSearch('best ai search engines', {
  gl: 'US',
  hl: 'en',
});

console.log('AI Overview Summary:', res.summary);
```

---

### 2. Google Trends Real-Time & Interest Over Time

Track market trends and trending searches in real time via the [Google Trends API](https://serpscraper.dev/google-trends-api):

```javascript
const { SerpScraperClient } = require('serpscraper');

const client = new SerpScraperClient('YOUR_API_KEY');

async function checkTrends() {
  // 1. Fetch live trending queries in the US
  const trends = await client.trendsNow({ gl: 'US' });

  console.log('🔥 Top Trending Searches:');
  for (const t of trends) {
    console.log(`#${t.position} ${t.keyword} (Traffic: ${t.traffic || 'N/A'})`);
  }

  // 2. Fetch Google Trends Interest Over Time
  const interest = await client.trendsInterest('deepseek', {
    gl: 'US',
    time: 'today 12-m',
  });

  console.log('\n📊 Interest Timeline Points:');
  for (const point of interest.timelineData) {
    console.log(`${point.date}: ${point.value}`);
  }
}

checkTrends();
```

---

## ⚡ Next.js App Router Integration

Easily integrate real-time search intelligence into a Next.js Server Component or Route Handler:

```typescript
// app/api/search/route.ts
import { NextResponse } from 'next/server';
import { SerpScraperClient } from 'serpscraper';

const client = new SerpScraperClient(process.env.SERPSCRAPER_API_KEY!);

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url);
  const q = searchParams.get('q') || 'artificial intelligence';

  try {
    const results = await client.googleSearch(q, { gl: 'US', hl: 'en' });

    return NextResponse.json({
      organic: results.data,
      ai_overview: results.aiOverview,
      summary: results.summary,
      execution_time: results.inSeconds,
    });
  } catch (error: any) {
    return NextResponse.json({ error: error.message }, { status: error.statusCode || 500 });
  }
}
```

---

## 🌐 Supported API Methods

All methods return a typed `SerpScraperResponse` instance:

| Method | API Endpoint | Documentation & Landing Page |
|---|---|---|
| `client.googleSearch(kw, opts)` | `webs-search` (engine=google) | [Google Search API](https://serpscraper.dev/web-search-api) |
| `client.webSearch(kw, opts)` | `webs-search` | [Web Search API](https://serpscraper.dev/web-search-api) |
| `client.aioSearch(kw, opts)` | `aio` | [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api) |
| `client.trendsNow(opts)` | `trends-now-search` | [Google Trends API (Real-Time)](https://serpscraper.dev/google-trends-api) |
| `client.trendsInterest(kw, opts)` | `trends-interest-search` | [Google Trends Interest Over Time](https://serpscraper.dev/google-trends-api) |
| `client.imageSearch(kw, opts)` | `images-search` | [Image Search API](https://serpscraper.dev/image-search-api) |
| `client.videoSearch(kw, opts)` | `videos-search` | [Video Search API](https://serpscraper.dev/video-search-api) |
| `client.newsSearch(kw, opts)` | `news-search` | [News Search API](https://serpscraper.dev/news-search-api) |
| `client.shoppingSearch(kw, opts)` | `shopping-search` | [Shopping Search API](https://serpscraper.dev/shopping-search-api) |
| `client.scholarSearch(kw, opts)` | `scholar-search` | [Google Scholar API](https://serpscraper.dev/scholar-search-api) |
| `client.mapsSearch(kw, opts)` | `maps-search` | [Google Maps API](https://serpscraper.dev/maps-search-api) |
| `client.reviewsSearch(kw, opts)` | `reviews-search` | [Reviews Search API](https://serpscraper.dev/reviews-search-api) |
| `client.autocomplete(kw, opts)` | `autocomplete` | [SERP Autocomplete Suggestions](https://serpscraper.dev/serp-api) |
| `client.extractWebpage(url)` | `webpage` | [Webpage Content Extractor API](https://serpscraper.dev/webpage-api) |
| `client.ipLookup(ip)` | `ip-lookup` | [IP Geolocation & Threat API](https://serpscraper.dev/ip-lookup-api) |
| `client.exchangeRate(from, to)` | `exchange-rate` | [Exchange Rate API](https://serpscraper.dev/exchange-rate-api) |
| `client.cryptoPrice(symbols, vs)` | `crypto-price` | [Cryptocurrency Price API](https://serpscraper.dev/crypto-price-api) |
| `client.domainInfo(domain)` | `domain-info` | [Domain Intelligence & WHOIS API](https://serpscraper.dev/domain-intelligence-api) |

---

## 🛡️ Error Handling

The SDK exposes custom error classes:

```javascript
const {
  SerpScraperClient,
  AuthenticationError,
  RateLimitError,
  SerpScraperError,
} = require('serpscraper');

const client = new SerpScraperClient('YOUR_API_KEY');

try {
  const results = await client.googleSearch('query');
} catch (err) {
  if (err instanceof AuthenticationError) {
    console.error('Invalid API token:', err.message);
  } else if (err instanceof RateLimitError) {
    console.error('Rate limit reached:', err.message);
  } else if (err instanceof SerpScraperError) {
    console.error(`API Error (${err.statusCode}):`, err.message);
  }
}
```

---

## 🧪 Testing

Run standard tests using Node.js built-in test runner:

```bash
npm test
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

The SerpScraper Node.js SDK is open-sourced software licensed under the [MIT License](../LICENSE).

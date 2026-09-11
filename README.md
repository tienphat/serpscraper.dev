# SerpScraper Multi-Language SDK: Real-Time Google SERP API, Google AI Overview & Trends (PHP, Python, Node.js, Go)

<p align="center">
  <a href="https://serpscraper.dev"><img src="https://serpscraper.dev/img/logo.png" width="110" alt="SerpScraper.Dev Logo"></a>
</p>

<p align="center">
  Official multi-language client libraries for <a href="https://serpscraper.dev"><strong>SerpScraper.Dev</strong></a> — The high-speed <a href="https://serpscraper.dev/serp-api">SERP API</a> designed for developers, SEO platforms, and AI agents.<br>
  Scrape real-time Google & Bing search rankings, parse <a href="https://serpscraper.dev/google-ai-overview-api">Google AI Overviews</a>, track <a href="https://serpscraper.dev/google-trends-api">Google Trends</a>, and extract web intelligence as clean, structured JSON.
</p>

<p align="center">
  <a href="https://packagist.org/packages/tienphat/serpscraper-php"><img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php" alt="PHP 8.1+"></a>
  <a href="https://pypi.org/project/serpscraper/"><img src="https://img.shields.io/badge/Python-3.8%2B-3776AB?logo=python" alt="Python 3.8+"></a>
  <a href="https://www.npmjs.com/package/serpscraper"><img src="https://img.shields.io/badge/Node.js-18%2B-339933?logo=node.js" alt="Node.js 18+"></a>
  <a href="https://pkg.go.dev/github.com/tienphat/serpscraper-php/go"><img src="https://img.shields.io/badge/Go-1.21%2B-00ADD8?logo=go" alt="Go 1.21+"></a>
  <img src="https://img.shields.io/badge/Dependencies-Zero-success" alt="Zero Dependencies">
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="MIT License"></a>
  <a href="https://serpscraper.dev"><img src="https://img.shields.io/badge/API%20Key-Free%20Tier-green" alt="Free API Key"></a>
</p>

---

## 🌟 Why Choose SerpScraper?

[SerpScraper.Dev](https://serpscraper.dev) is built from the ground up for modern developers, LLM engineers, and SEO specialists who require reliable search data at scale:

- ⚡ **Sub-Second Response Times**: Engineered with distributed high-performance edge nodes for ultra-fast query execution.
- 🛡️ **Zero Block Guarantee**: Built-in rotating residential and datacenter proxies completely bypass Cloudflare, Akamai, Google reCAPTCHA, and bot detection systems.
- 🤖 **Native Google AI Overview Extraction**: Directly extract synthesized AI answers, citation cards, and source links via the [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api) — perfect for RAG and AI search workflows.
- 📈 **Real-Time Google Trends**: Monitor breaking news, trending searches, and historical search volume timelines with the [Google Trends API](https://serpscraper.dev/google-trends-api).
- 🌍 **Global Geolocation Targeting**: Precise local search emulation across 190+ countries (`gl`), languages (`hl`), and regional domains (`google.co.uk`, `google.co.jp`, etc.).
- 📦 **Zero External Dependencies**: All SDKs in this monorepo rely strictly on standard libraries (cURL, urllib, native fetch, net/http) for maximum security and zero dependency conflicts.

---

## 📁 Monorepo Structure

This repository provides official client libraries across four major programming languages:

```
serpscraper/
├── php/        # 🐘 PHP SDK (Composer: tienphat/serpscraper-php) -> php/README.md
├── python/     # 🐍 Python SDK (PyPI: serpscraper)               -> python/README.md
├── nodejs/     # 🟩 Node.js / TypeScript SDK (npm: serpscraper)  -> nodejs/README.md
├── go/         # 🐹 Go SDK (Go Modules: .../go)                 -> go/README.md
├── composer.json # Root Composer support for backward-compatible installs
└── README.md
```

| Language | Package Manager | Installation | Detailed Guide |
|---|---|---|---|
| **PHP** | Packagist / Composer | `composer require tienphat/serpscraper-php` | [`php/README.md`](php/README.md) |
| **Python** | PyPI / pip | `pip install serpscraper` | [`python/README.md`](python/README.md) |
| **Node.js / TS** | npm / yarn / pnpm | `npm install serpscraper` | [`nodejs/README.md`](nodejs/README.md) |
| **Go** | Go Modules | `go get github.com/tienphat/serpscraper-php/go` | [`go/README.md`](go/README.md) |

---

## 🚀 Quickstarts

Grab your free API key at [serpscraper.dev](https://serpscraper.dev).

### 🐘 1. PHP SDK

```bash
composer require tienphat/serpscraper-php
```

```php
use SerpScraper\SerpScraperClient;

$client = new SerpScraperClient('YOUR_API_KEY');

// 1. Google Web Search & AI Overview
$results = $client->googleSearch('best generative ai tools', ['gl' => 'US', 'hl' => 'en']);

foreach ($results as $item) {
    echo "#{$item['position']} {$item['title']} — {$item['link']}\n";
}

if ($summary = $results->summary()) {
    echo "\n[Google AI Overview]:\n{$summary}\n";
}

// 2. Google Trends Real-Time
$trends = $client->trendsNow(['gl' => 'US']);
echo "Top Trend: " . $trends->first()['keyword'] . "\n";
```
👉 *Read the complete [PHP SDK Documentation](php/README.md).*

---

### 🐍 2. Python SDK

```bash
pip install serpscraper
```

```python
from serpscraper import SerpScraperClient

client = SerpScraperClient("YOUR_API_KEY")

# 1. Google Web Search & AI Overview
results = client.google_search("best generative ai tools", gl="US", hl="en")

for item in results:
    print(f"#{item['position']} {item['title']} — {item['link']}")

if results.summary:
    print(f"\n[Google AI Overview]:\n{results.summary}")

# 2. Google Trends Real-Time
trends = client.trends_now(gl="US")
print(f"Top Trend: {trends.first['keyword']}")
```
👉 *Read the complete [Python SDK Documentation](python/README.md).*

---

### 🟩 3. Node.js & TypeScript SDK

```bash
npm install serpscraper
```

```javascript
const { SerpScraperClient } = require('serpscraper');

const client = new SerpScraperClient('YOUR_API_KEY');

async function main() {
  // 1. Google Web Search & AI Overview
  const res = await client.googleSearch('best generative ai tools', { gl: 'US', hl: 'en' });

  for (const item of res) {
    console.log(`#${item.position} ${item.title} — ${item.link}`);
  }

  if (res.summary) {
    console.log('\n[Google AI Overview]:\n', res.summary);
  }

  // 2. Google Trends Real-Time
  const trends = await client.trendsNow({ gl: 'US' });
  console.log('Top Trend:', trends.first?.keyword);
}

main();
```
👉 *Read the complete [Node.js SDK Documentation](nodejs/README.md).*

---

### 🐹 4. Go SDK

```bash
go get github.com/tienphat/serpscraper-php/go
```

```go
package main

import (
	"context"
	"fmt"
	"time"

	serpscraper "github.com/tienphat/serpscraper-php/go"
)

func main() {
	client, _ := serpscraper.NewClient("YOUR_API_KEY")
	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()

	// 1. Google Web Search & AI Overview
	resp, _ := client.GoogleSearch(ctx, "best generative ai tools",
		serpscraper.WithGL("US"),
		serpscraper.WithHL("en"),
	)

	for _, item := range resp.Data {
		fmt.Printf("#%d %s — %s\n", item.Position, item.Title, item.Link)
	}

	if summary := resp.Summary(); summary != "" {
		fmt.Printf("\n[Google AI Overview]:\n%s\n", summary)
	}

	// 2. Google Trends Real-Time
	trends, _ := client.TrendsNow(ctx, serpscraper.WithGL("US"))
	if len(trends.Data) > 0 {
		fmt.Printf("Top Trend: %s\n", trends.Data[0].Keyword)
	}
}
```
👉 *Read the complete [Go SDK Documentation](go/README.md).*

---

## 🌐 Supported APIs & Live Endpoints

All SDKs map directly to the high-performance endpoints hosted at [serpscraper.dev](https://serpscraper.dev):

| API Feature | PHP / Node / Go Method | Python Method | Landing Page & Documentation |
|---|---|---|---|
| **Google Search API** | `googleSearch()` / `GoogleSearch` | `google_search()` | [Google Search API](https://serpscraper.dev/web-search-api) |
| **Web Search API** | `webSearch()` / `WebSearch` | `web_search()` | [Web Search API (Bing / Multi-Engine)](https://serpscraper.dev/web-search-api) |
| **Google AI Overview API** | `aioSearch()` / `AioSearch` | `aio_search()` | [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api) |
| **Google Trends Real-Time** | `trendsNow()` / `TrendsNow` | `trends_now()` | [Google Trends API](https://serpscraper.dev/google-trends-api) |
| **Google Trends Interest** | `trendsInterest()` / `TrendsInterest` | `trends_interest()` | [Google Trends Interest Over Time](https://serpscraper.dev/google-trends-api) |
| **Image Search API** | `imageSearch()` / `ImageSearch` | `image_search()` | [Image Search API](https://serpscraper.dev/image-search-api) |
| **Video Search API** | `videoSearch()` / `VideoSearch` | `video_search()` | [Video Search API](https://serpscraper.dev/video-search-api) |
| **News Search API** | `newsSearch()` / `NewsSearch` | `news_search()` | [News Search API](https://serpscraper.dev/news-search-api) |
| **Shopping Search API** | `shoppingSearch()` / `ShoppingSearch` | `shopping_search()` | [Shopping Search API](https://serpscraper.dev/shopping-search-api) |
| **Google Scholar API** | `scholarSearch()` / `ScholarSearch` | `scholar_search()` | [Google Scholar API](https://serpscraper.dev/scholar-search-api) |
| **Google Maps API** | `mapsSearch()` / `MapsSearch` | `maps_search()` | [Google Maps & Places API](https://serpscraper.dev/maps-search-api) |
| **Reviews Search API** | `reviewsSearch()` / `ReviewsSearch` | `reviews_search()` | [Customer Reviews Search API](https://serpscraper.dev/reviews-search-api) |
| **SERP Autocomplete** | `autocomplete()` / `Autocomplete` | `autocomplete()` | [SERP Autocomplete API](https://serpscraper.dev/serp-api) |
| **Webpage Extractor** | `extractWebpage()` / `ExtractWebpage` | `extract_webpage()` | [Webpage Content Extractor API](https://serpscraper.dev/webpage-api) |
| **IP Geolocation API** | `ipLookup()` / `IPLookup` | `ip_lookup()` | [IP Geolocation & Threat API](https://serpscraper.dev/ip-lookup-api) |
| **Exchange Rate API** | `exchangeRate()` / `ExchangeRate` | `exchange_rate()` | [Fiat Exchange Rate API](https://serpscraper.dev/exchange-rate-api) |
| **Crypto Price API** | `cryptoPrice()` / `CryptoPrice` | `crypto_price()` | [Cryptocurrency Price API](https://serpscraper.dev/crypto-price-api) |
| **Domain Intelligence** | `domainInfo()` / `DomainInfo` | `domain_info()` | [Domain Intelligence & WHOIS API](https://serpscraper.dev/domain-intelligence-api) |

---

## 🎯 Key Use Cases

### 1. 🤖 AI Agents & Retrieval-Augmented Generation (RAG)
Equip LLMs (OpenAI GPT-4o, Claude 3.5, Llama 3, Gemini 1.5) with real-time web knowledge. Extract both organic sources and synthesized [Google AI Overviews](https://serpscraper.dev/google-ai-overview-api) with zero hallucination risk.

### 2. 📊 SEO Rank Tracking & Competitor Intelligence
Monitor organic rankings, SERP features (Featured Snippets, People Also Ask, Local Packs), and track ranking fluctuations across 190+ countries with exact local IP emulation via the [SERP API](https://serpscraper.dev/serp-api).

### 3. 🔥 Trend Detection & Market Research
Discover viral topics before competitors with real-time trending queries from the [Google Trends API](https://serpscraper.dev/google-trends-api). Analyze multi-year historical search interest to validate product demand.

### 4. 🛒 E-Commerce Price Monitoring & Review Aggregation
Track product prices, availability, and merchant ratings across Google Shopping with the [Shopping Search API](https://serpscraper.dev/shopping-search-api) and [Reviews Search API](https://serpscraper.dev/reviews-search-api).

---

## ❓ Frequently Asked Questions (FAQ)

<details>
<summary><strong>1. How does SerpScraper bypass Google CAPTCHA and bot detection?</strong></summary>
SerpScraper manages an enterprise-scale pool of rotating residential proxies and AI-driven anti-fingerprinting browsers. All CAPTCHA solving, IP rotation, and header spoofing are handled automatically on the server side, ensuring consistent 99.9% uptime and unblockable scraping.
</details>

<details>
<summary><strong>2. Can I parse Google AI Overviews with this SDK?</strong></summary>
Yes! SerpScraper provides native parsing for Google AI Overviews (SGE). Every response object includes direct helper methods like <code>$results->summary()</code>, <code>results.summary</code>, or <code>resp.Summary()</code> to immediately extract AI-synthesized markdown text and cited references. Learn more at <a href="https://serpscraper.dev/google-ai-overview-api">serpscraper.dev/google-ai-overview-api</a>.
</details>

<details>
<summary><strong>3. Are there any external runtime dependencies in these SDKs?</strong></summary>
None. All SDKs are implemented with pure standard libraries: PHP cURL, Python <code>urllib</code>, Node.js native <code>fetch</code>, and Go <code>net/http</code>. This ensures zero dependency conflicts, lightweight footprints, and optimal security.
</details>

<details>
<summary><strong>4. How do I obtain a free API key?</strong></summary>
You can sign up and get your free API key instantly with generous monthly credits at <a href="https://serpscraper.dev">serpscraper.dev</a>.
</details>

---

## 🧪 Testing

Unit and integration test suites are available in each respective language directory:

```bash
# 1. PHP (PHPUnit 11 — 67 tests, 114 assertions)
cd php && vendor/bin/phpunit

# 2. Python (unittest — 5 tests)
cd python && python -m unittest discover -s tests

# 3. Node.js (native test runner — 2 tests)
cd nodejs && npm test

# 4. Go (testing package)
cd go && go test -v ./...
```

---

## 📚 Documentation & Resources

- 🌐 **Official Website**: [https://serpscraper.dev](https://serpscraper.dev)
- 📖 **API Documentation**: [https://serpscraper.dev/docs](https://serpscraper.dev/docs)
- 🔑 **Get Free API Key**: [https://serpscraper.dev](https://serpscraper.dev)
- 📊 **SERP API Landing Page**: [https://serpscraper.dev/serp-api](https://serpscraper.dev/serp-api)
- 🤖 **Google AI Overview API**: [https://serpscraper.dev/google-ai-overview-api](https://serpscraper.dev/google-ai-overview-api)
- 📈 **Google Trends API**: [https://serpscraper.dev/google-trends-api](https://serpscraper.dev/google-trends-api)
- 💬 **Telegram Support**: [@peterpanpro](https://t.me/peterpanpro)
- 🐛 **Issue Tracker**: [GitHub Issues](https://github.com/tienphat/serpscraper-php/issues)

---

## 📄 License

The SerpScraper SDK Monorepo is open-sourced software licensed under the [MIT License](LICENSE).

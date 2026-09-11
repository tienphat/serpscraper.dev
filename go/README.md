# SerpScraper Go SDK — High-Performance Google SERP & Search Intelligence API

<p align="center">
  <a href="https://serpscraper.dev"><img src="https://serpscraper.dev/img/logo.png" width="100" alt="SerpScraper.Dev Logo"></a>
</p>

<p align="center">
  Official <strong>Go</strong> client library for <a href="https://serpscraper.dev"><strong>SerpScraper.Dev</strong></a> — The developer-first <a href="https://serpscraper.dev/serp-api">SERP API</a> for real-time Google search results, <a href="https://serpscraper.dev/google-ai-overview-api">Google AI Overviews</a>, and <a href="https://serpscraper.dev/google-trends-api">Google Trends</a> data.
</p>

<p align="center">
  <a href="https://pkg.go.dev/github.com/tienphat/serpscraper-php/go"><img src="https://pkg.go.dev/badge/github.com/tienphat/serpscraper-php/go.svg" alt="Go Reference"></a>
  <img src="https://img.shields.io/badge/Go-1.21%2B-00ADD8?logo=go" alt="Go 1.21+">
  <img src="https://img.shields.io/badge/Dependencies-Zero-success" alt="Zero Dependencies">
  <a href="../LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="MIT License"></a>
  <a href="https://serpscraper.dev/api-key"><img src="https://img.shields.io/badge/API%20Key-Free%20Tier-green" alt="Free API Key"></a>
</p>

---

## ⚡ Why Use SerpScraper Go SDK?

The `serpscraper` Go SDK provides blazing-fast, concurrent access to the [SerpScraper.Dev API](https://serpscraper.dev). Designed with Go idiomatic patterns:

- 🏎️ **Zero External Dependencies**: Implemented strictly with Go standard library (`net/http`, `context`, `encoding/json`). No bloated dependency trees.
- 🛡️ **Thread-Safe & Production-Ready**: Safe for high-concurrency microservices, crawlers, and AI pipelines.
- ⏱️ **First-Class Context Support**: Native `context.Context` handling on all API calls for deadline propagation and graceful cancellation.
- 🤖 **Native Google AI Overview Extraction**: Parse synthesized AI answers, citation cards, and markdown content effortlessly via [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api).
- 📈 **Real-Time Google Trends**: Query live trending queries and historical interest timelines with the [Google Trends API](https://serpscraper.dev/google-trends-api).
- 🌍 **Global Geolocation Targeting**: Emulate user searches across 190+ countries (`gl`) and languages (`hl`) with residential IP proxy rotation.

---

## 📦 Installation

```bash
go get github.com/tienphat/serpscraper-php/go
```

---

## 🚀 Quick Start

Get your free API key at [serpscraper.dev/api-key](https://serpscraper.dev/api-key).

### 1. Google Web Search & AI Overview Extraction

Scrape organic rankings and extracted AI Overviews directly into structured Go structs:

```go
package main

import (
	"context"
	"fmt"
	"log"
	"time"

	serpscraper "github.com/tienphat/serpscraper-php/go"
)

func main() {
	client, err := serpscraper.NewClient("YOUR_API_KEY")
	if err != nil {
		log.Fatalf("Failed to initialize client: %v", err)
	}

	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()

	// Search Google for real-time organic rankings & AI synthesis
	resp, err := client.GoogleSearch(ctx, "latest advances in quantum computing",
		serpscraper.WithGL("US"),
		serpscraper.WithHL("en"),
		serpscraper.WithLimit(10),
	)
	if err != nil {
		log.Fatalf("Search failed: %v", err)
	}

	// 1. Inspect Organic Search Results
	fmt.Printf("Fetched %d results in %.2fs\n\n", resp.Length(), resp.InSeconds)
	for _, item := range resp.Data {
		fmt.Printf("#%d: %s\n    URL: %s\n\n", item.Position, item.Title, item.Link)
	}

	// 2. Inspect Google AI Overview Summary & Citations
	if summary := resp.Summary(); summary != "" {
		fmt.Println("──────────────────────────────────────────")
		fmt.Printf("🤖 [Google AI Overview]:\n%s\n", summary)
		fmt.Println("──────────────────────────────────────────")
		fmt.Println("Cited Sources:")
		for _, src := range resp.Sources() {
			fmt.Printf(" - %s (%s)\n", src.Title, src.Link)
		}
	}
}
```

---

### 2. Google Trends Real-Time & Interest Over Time

Monitor break-out search trends and consumer sentiment in real time via the [Google Trends API](https://serpscraper.dev/google-trends-api):

```go
package main

import (
	"context"
	"fmt"
	"log"
	"time"

	serpscraper "github.com/tienphat/serpscraper-php/go"
)

func main() {
	client, _ := serpscraper.NewClient("YOUR_API_KEY")
	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()

	// 1. Fetch live trending searches in the United States
	trends, err := client.TrendsNow(ctx, serpscraper.WithGL("US"))
	if err != nil {
		log.Fatalf("Trends failed: %v", err)
	}

	fmt.Println("🔥 Top Trending Searches Right Now:")
	for _, item := range trends.Data {
		fmt.Printf("#%d: %s\n", item.Position, item.Keyword)
	}

	// 2. Fetch Interest Over Time for a specific keyword
	interest, err := client.TrendsInterest(ctx, "bitcoin",
		serpscraper.WithGL("US"),
		serpscraper.WithCustom("time", "today 12-m"),
	)
	if err != nil {
		log.Fatalf("Interest search failed: %v", err)
	}

	fmt.Println("\n📊 Interest Timeline Points:")
	for _, pt := range interest.TimelineData() {
		fmt.Printf("%s: %v\n", pt.Date, pt.Value)
	}
}
```

---

## 🛠️ Advanced Configuration

### Custom Base URL, Timeouts & Defaults

```go
cfg := serpscraper.NewConfig("YOUR_API_KEY").
	WithCountry("US").
	WithLanguage("en").
	WithTimeout(15 * time.Second)

client, err := serpscraper.NewClientWithConfig(cfg)
```

### Functional Options

Customize any API call dynamically using functional options:

```go
resp, err := client.WebSearch(ctx, "best CRM software",
	serpscraper.WithEngine("google"), // "google" or "bing"
	serpscraper.WithCountry("GB"),     // Target United Kingdom
	serpscraper.WithLanguage("en"),    // English language
	serpscraper.WithLimit(20),         // Up to 20 results
	serpscraper.WithPage(2),          // Pagination
	serpscraper.WithDevice("mobile"), // Emulate mobile device
	serpscraper.WithCustom("safe", "active"),
)
```

---

## 🌐 Supported API Methods

All endpoints return strongly typed `*serpscraper.Response` structs with convenient helper methods:

| Go Method | API Endpoint | Documentation & Landing Page |
|---|---|---|
| `client.GoogleSearch(ctx, kw, opts...)` | `webs-search` (engine=google) | [Google Search API](https://serpscraper.dev/web-search-api) |
| `client.WebSearch(ctx, kw, opts...)` | `webs-search` | [Web Search API](https://serpscraper.dev/web-search-api) |
| `client.AioSearch(ctx, kw, opts...)` | `aio` | [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api) |
| `client.TrendsNow(ctx, opts...)` | `trends-now-search` | [Google Trends API (Real-Time)](https://serpscraper.dev/google-trends-api) |
| `client.TrendsInterest(ctx, kw, opts...)` | `trends-interest-search` | [Google Trends Interest Over Time](https://serpscraper.dev/google-trends-api) |
| `client.ImageSearch(ctx, kw, opts...)` | `images-search` | [Image Search API](https://serpscraper.dev/image-search-api) |
| `client.VideoSearch(ctx, kw, opts...)` | `videos-search` | [Video Search API](https://serpscraper.dev/video-search-api) |
| `client.NewsSearch(ctx, kw, opts...)` | `news-search` | [News Search API](https://serpscraper.dev/news-search-api) |
| `client.ShoppingSearch(ctx, kw, opts...)` | `shopping-search` | [Shopping Search API](https://serpscraper.dev/shopping-search-api) |
| `client.ScholarSearch(ctx, kw, opts...)` | `scholar-search` | [Google Scholar API](https://serpscraper.dev/scholar-search-api) |
| `client.MapsSearch(ctx, kw, opts...)` | `maps-search` | [Google Maps API](https://serpscraper.dev/maps-search-api) |
| `client.ReviewsSearch(ctx, kw, opts...)` | `reviews-search` | [Reviews Search API](https://serpscraper.dev/reviews-search-api) |
| `client.Autocomplete(ctx, kw, opts...)` | `autocomplete` | [SERP Autocomplete Suggestions](https://serpscraper.dev/serp-api) |
| `client.ExtractWebpage(ctx, url, opts...)` | `webpage` | [Webpage Content Extractor API](https://serpscraper.dev/webpage-api) |
| `client.IPLookup(ctx, ip, opts...)` | `ip-lookup` | [IP Geolocation & Threat API](https://serpscraper.dev/ip-lookup-api) |
| `client.ExchangeRate(ctx, from, to, opts...)` | `exchange-rate` | [Exchange Rate API](https://serpscraper.dev/exchange-rate-api) |
| `client.CryptoPrice(ctx, symbols, vs, opts...)` | `crypto-price` | [Cryptocurrency Price API](https://serpscraper.dev/crypto-price-api) |
| `client.DomainInfo(ctx, domain, opts...)` | `domain-info` | [Domain Intelligence & WHOIS API](https://serpscraper.dev/domain-intelligence-api) |

---

## 🤖 Use Case: AI Agents & RAG with Go

Enhance your Go-based LLM applications (LangChainGo, Ollama, OpenAI Go SDK) with real-time web search ground truth:

```go
func searchWebForLLM(ctx context.Context, client *serpscraper.Client, userQuery string) (string, error) {
	resp, err := client.GoogleSearch(ctx, userQuery, serpscraper.WithLimit(5))
	if err != nil {
		return "", err
	}

	contextText := ""
	if summary := resp.Summary(); summary != "" {
		contextText += fmt.Sprintf("AI Overview Summary:\n%s\n\n", summary)
	}

	contextText += "Top Web Sources:\n"
	for _, item := range resp.Data {
		contextText += fmt.Sprintf("- [%s](%s): %s\n", item.Title, item.Link, item.Snippet)
	}

	return contextText, nil
}
```

---

## 🧪 Testing

Run standard unit and mock server tests:

```bash
go test -v ./...
```

---

## 📚 Resources & Documentation

- 🌐 **Website**: [https://serpscraper.dev](https://serpscraper.dev)
- 📖 **Official API Documentation**: [https://serpscraper.dev/docs](https://serpscraper.dev/docs)
- 🔑 **Get Free API Key**: [https://serpscraper.dev/api-key](https://serpscraper.dev/api-key)
- 🐛 **Issue Tracker**: [GitHub Issues](https://github.com/tienphat/serpscraper-php/issues)
- 💬 **Community Support**: [Telegram @peterpanpro](https://t.me/peterpanpro)

---

## 📄 License

The SerpScraper Go SDK is open-sourced software licensed under the [MIT License](../LICENSE).

# serpscraper: Official Python SDK for SerpScraper.Dev (Google SERP API, AI Overview & Trends)

<p align="center">
  <a href="https://serpscraper.dev"><img src="https://serpscraper.dev/img/logo.png" width="100" alt="SerpScraper.Dev Logo"></a>
</p>

<p align="center">
  Official <strong>Python</strong> client library for <a href="https://serpscraper.dev"><strong>SerpScraper.Dev</strong></a> — a fast <a href="https://serpscraper.dev/serp-api">SERP API</a> for real-time Google search rankings, <a href="https://serpscraper.dev/google-ai-overview-api">Google AI Overviews</a>, and <a href="https://serpscraper.dev/google-trends-api">Google Trends</a>.
</p>

<p align="center">
  <a href="https://pypi.org/project/serpscraper/"><img src="https://img.shields.io/pypi/v/serpscraper.svg" alt="PyPI Version"></a>
  <img src="https://img.shields.io/badge/Python-3.8%2B-3776AB?logo=python" alt="Python 3.8+">
  <img src="https://img.shields.io/badge/Dependencies-Zero-success" alt="Zero Dependencies">
  <a href="../LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="MIT License"></a>
  <a href="https://serpscraper.dev"><img src="https://img.shields.io/badge/API%20Key-Free%20Tier-green" alt="Free API Key"></a>
</p>

---

## ⚡ Why Use `serpscraper` for Python?

- 🐍 **Zero External Dependencies**: Built 100% on Python standard library (`urllib.request`, `json`). Zero conflict with requests, httpx, or aiohttp versions in your virtual environment.
- 🤖 **Native Google AI Overview Extraction**: Directly inspect synthesized AI answers, citation cards, and markdown content via the [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api) with `results.summary` and `results.sources`.
- 📈 **Real-Time Google Trends**: Monitor live trending searches and historical search interest with the [Google Trends API](https://serpscraper.dev/google-trends-api).
- 🧠 **AI & LLM Ready**: Perfect for LangChain, LlamaIndex, AutoGen, and CrewAI web-search grounding and RAG pipelines.
- 🌍 **Global Geolocation Targeting**: Target searches from 190+ countries (`gl`) and all languages (`hl`).
- 🛡️ **Bypass Bot Protection**: Rotating residential IP pool completely bypasses Cloudflare, Akamai, and Google CAPTCHAs.

---

## 📦 Installation

Install via pip:

```bash
pip install serpscraper
```

Or install from source in editable mode:

```bash
pip install -e .
```

---

## 🚀 Quick Start

Get your free API key at [serpscraper.dev](https://serpscraper.dev).

### 1. Google Web Search & AI Overview Extraction

```python
from serpscraper import SerpScraperClient

client = SerpScraperClient("YOUR_API_KEY")

# Scrape Google organic results + AI Overview targeting US English
results = client.google_search("best vector database for rag", gl="US", hl="en")

print(f"Fetched {len(results)} results in {results.in_seconds}s:\n")

# 1. Iterate through Organic Search Rankings
for item in results:
    print(f"#{item['position']} {item['title']}")
    print(f"    URL: {item['link']}")
    print(f"    Snippet: {item.get('snippet', '')}\n")

# 2. Extract Google AI Overview (SGE) & Sources
if results.summary:
    print("──────────────────────────────────────────")
    print(f"🤖 [Google AI Overview]:\n{results.summary}")
    print("──────────────────────────────────────────")
    print("Cited Sources:")
    for src in results.sources:
        print(f" - {src['title']} ({src['link']})")
```

---

### 2. Google Trends Real-Time & Interest Over Time

Track market trends and trending searches in real time via the [Google Trends API](https://serpscraper.dev/google-trends-api):

```python
from serpscraper import SerpScraperClient

client = SerpScraperClient("YOUR_API_KEY")

# 1. Fetch live trending queries in the US
trends = client.trends_now(gl="US")

print("🔥 Top Trending Searches:")
for t in trends:
    traffic = t.get("traffic", "N/A")
    print(f"#{t['position']} {t['keyword']} (Search volume: {traffic})")

# 2. Fetch Google Trends Interest Over Time
interest = client.trends_interest("deepseek", gl="US", time="today 12-m")

print("\n📊 Interest Timeline Points:")
for point in interest.timeline_data:
    print(f"{point['date']}: {point['value']}")
```

---

## 🤖 Use Case: LangChain & LLM Tool Integration

Turn SerpScraper into a live web search tool for OpenAI, Anthropic, or local LLMs:

```python
from serpscraper import SerpScraperClient

client = SerpScraperClient("YOUR_API_KEY")

def serp_search_tool(query: str) -> str:
    """Useful for searching the live web and getting real-time information."""
    results = client.google_search(query, gl="US", hl="en")
    
    output = []
    if results.summary:
        output.append(f"AI Overview Summary:\n{results.summary}\n")
        
    output.append("Top Web Results:")
    for item in results[:5]:
        output.append(f"- {item['title']}: {item['snippet']} ({item['link']})")
        
    return "\n".join(output)

# Ready to pass as a tool into LangChain, LlamaIndex, or AutoGen!
```

---

## 🌐 Supported API Methods

All methods return a clean `SerpScraperResponse` object with intuitive helpers:

| Python Method | API Endpoint | Documentation & Landing Page |
|---|---|---|
| `client.google_search(kw, **opts)` | `webs-search` (engine=google) | [Google Search API](https://serpscraper.dev/web-search-api) |
| `client.web_search(kw, **opts)` | `webs-search` | [Web Search API](https://serpscraper.dev/web-search-api) |
| `client.aio_search(kw, **opts)` | `aio` | [Google AI Overview API](https://serpscraper.dev/google-ai-overview-api) |
| `client.trends_now(**opts)` | `trends-now-search` | [Google Trends API (Real-Time)](https://serpscraper.dev/google-trends-api) |
| `client.trends_interest(kw, **opts)` | `trends-interest-search` | [Google Trends Interest Over Time](https://serpscraper.dev/google-trends-api) |
| `client.image_search(kw, **opts)` | `images-search` | [Image Search API](https://serpscraper.dev/image-search-api) |
| `client.video_search(kw, **opts)` | `videos-search` | [Video Search API](https://serpscraper.dev/video-search-api) |
| `client.news_search(kw, **opts)` | `news-search` | [News Search API](https://serpscraper.dev/news-search-api) |
| `client.shopping_search(kw, **opts)` | `shopping-search` | [Shopping Search API](https://serpscraper.dev/shopping-search-api) |
| `client.scholar_search(kw, **opts)` | `scholar-search` | [Google Scholar API](https://serpscraper.dev/scholar-search-api) |
| `client.maps_search(kw, **opts)` | `maps-search` | [Google Maps API](https://serpscraper.dev/maps-search-api) |
| `client.reviews_search(kw, **opts)` | `reviews-search` | [Reviews Search API](https://serpscraper.dev/reviews-search-api) |
| `client.autocomplete(kw, **opts)` | `autocomplete` | [SERP Autocomplete Suggestions](https://serpscraper.dev/serp-api) |
| `client.extract_webpage(url)` | `webpage` | [Webpage Content Extractor API](https://serpscraper.dev/webpage-api) |
| `client.ip_lookup(ip)` | `ip-lookup` | [IP Geolocation & Threat API](https://serpscraper.dev/ip-lookup-api) |
| `client.exchange_rate(from_c, to_c)` | `exchange-rate` | [Exchange Rate API](https://serpscraper.dev/exchange-rate-api) |
| `client.crypto_price(symbols, vs)` | `crypto-price` | [Cryptocurrency Price API](https://serpscraper.dev/crypto-price-api) |
| `client.domain_info(domain)` | `domain-info` | [Domain Intelligence & WHOIS API](https://serpscraper.dev/domain-intelligence-api) |

---

## 🛡️ Error Handling

Catch specific exceptions from `serpscraper.exceptions`:

```python
from serpscraper import SerpScraperClient
from serpscraper.exceptions import (
    AuthenticationError,
    RateLimitError,
    NetworkError,
    SerpScraperError,
)

client = SerpScraperClient("YOUR_API_KEY")

try:
    results = client.google_search("query")
except AuthenticationError as e:
    print("Invalid API Key:", e)
except RateLimitError as e:
    print("Credit limit exceeded:", e)
except NetworkError as e:
    print("Connection failed:", e)
except SerpScraperError as e:
    print("API Error:", e)
```

---

## 🧪 Testing

Run standard unit tests:

```bash
python -m unittest discover -s tests
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

The SerpScraper Python SDK is open-sourced software licensed under the [MIT License](../LICENSE).

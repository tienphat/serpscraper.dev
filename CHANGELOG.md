# Changelog

All notable changes to `tienphat/serpscraper-php` are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] — 2026-09-11

### Changed
- Rebranded and updated package name to `tienphat/serpscraper-php`.
- Updated git repository to `git@github.com:tienphat/serpscraper-php.git`.
- Changed default base URL to `https://serpscraper.dev/api/v1`.
- Supported both `SERPSCRAPER_*` and `SERPAPI_*` environment variables.

### Added
- **Multi-Language SDK Monorepo**: Reorganized repository to house dedicated SDKs in `php/`, `python/`, `nodejs/`, and `go/`.
- **Go SDK**: Added official Golang SDK (`github.com/tienphat/serpscraper-php/go`) with zero external dependencies, thread-safety, and `context.Context` support.
- **Python SDK**: Added official Python SDK (`serpscraper`) with zero external dependencies and AI/RAG integration support.
- **Node.js / TypeScript SDK**: Added official Node.js SDK (`serpscraper`) with zero external dependencies and bundled `.d.ts` type declarations.
- **Google Search**: Added `googleSearch($keyword, $options)` for organic search + Google AI Overview extraction.
- **AI Overview**: Added `aioSearch($keyword, $options)` for standalone AI Overview answers.
- **Google Trends**: Added `trendsNow($options)` for real-time trending searches by country.
- **Google Trends**: Added `trendsInterest($keyword, $options)` for Google Trends interest over time.
- **Response Accessors**: Added `aiOverview()`, `summary()`, `sources()`, and `timelineData()` helpers across all SDKs.
- **SEO-Optimized Documentation**: Built comprehensive READMEs for the monorepo root and each language folder, with contextual backlinks to `https://serpscraper.dev` landing pages.
- Expanded PHP test suite to 67 tests and 114 assertions (100% passing).

## [1.0.0] — 2025-07-17

### Added
- Initial release with `webSearch()`, `imageSearch()`, `videoSearch()`, `newsSearch()`, `shoppingSearch()`, `autocomplete()`, `scholarSearch()`, `mapsSearch()`, `reviewsSearch()`, `search()`, `extractWebpage()`.
- Added Utility endpoints: `ipLookup()`, `exchangeRate()`, `cryptoPrice()`, `domainInfo()`.
- Typed exceptions: `SerpApiException`, `AuthException`, `RateLimitException`, `NetworkException`.
- Zero runtime dependencies — pure PHP + cURL.

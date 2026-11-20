"""SerpScraper client implementation in pure Python (zero external dependencies)."""

import json
import urllib.error
import urllib.parse
import urllib.request
from typing import Any, Dict, Optional, Union

from .config import SerpScraperConfig
from .exceptions import (
    AuthException,
    NetworkException,
    RateLimitException,
    SerpScraperException,
)
from .response import SerpScraperResponse


class SerpScraperClient:
    """Official Python Client for SerpScraper.Dev.

    Usage:
        client = SerpScraperClient("your-api-key")
        results = client.google_search("openai o3", gl="US", hl="en")
        for item in results:
            print(item["title"], item["link"])
    """

    def __init__(self, config: Union[SerpScraperConfig, str]):
        if isinstance(config, str):
            self.config = SerpScraperConfig(config)
        else:
            self.config = config

    # ── Search Endpoints ──────────────────────────────────────────────────────

    def google_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Search Google Organic rankings + extract Google AI Overview."""
        opts = {"engine": "google", **options}
        return self.web_search(keyword, **opts)

    def web_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Web search results (Bing default or engine='google')."""
        return self._call("webs-search", {"keyword": keyword, **options})

    def aio_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Standalone AI Overview synthesis (Gemini / Google Grounding or Bing AIO)."""
        return self._call("aio", {"keyword": keyword, **options})

    def trends_now(self, **options: Any) -> SerpScraperResponse:
        """Google Trends Real-Time trending searches by country (gl='US')."""
        return self._call("trends-now-search", options)

    def trends_interest(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Google Trends Interest Over Time (time='today 12-m', gl='US')."""
        return self._call("trends-interest-search", {"keyword": keyword, **options})

    def image_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Image results with thumbnails and source URLs."""
        return self._call("images-search", {"keyword": keyword, **options})

    def video_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Video results with duration, views, and provider."""
        return self._call("videos-search", {"keyword": keyword, **options})

    def news_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """News articles with source and publication time."""
        return self._call("news-search", {"keyword": keyword, **options})

    def shopping_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """E-commerce product results with price and merchant."""
        return self._call("shopping-search", {"keyword": keyword, **options})

    def autocomplete(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Search query auto-suggestions."""
        return self._call("autocomplete", {"keyword": keyword, **options})

    def scholar_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Google Scholar scientific publications and citations."""
        return self._call("scholar-search", {"keyword": keyword, **options})

    def maps_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Local maps and places with addresses and phone numbers."""
        return self._call("maps-search", {"keyword": keyword, **options})

    def reviews_search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Review results with rating signals."""
        return self._call("reviews-search", {"keyword": keyword, **options})

    def search(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Unified endpoint — auto-detects type or SerpApi format."""
        return self._call("search", {"keyword": keyword, **options})

    def extract_webpage(self, url: str, include_html: bool = False) -> SerpScraperResponse:
        """Extract content, metadata, and links from any target URL."""
        params: Dict[str, Any] = {"url": url}
        if include_html:
            params["include_html"] = "true"
        return self._call("webpage", params)

    # ── Utility Endpoints ─────────────────────────────────────────────────────

    def ip_lookup(self, ip: Optional[str] = None, **options: Any) -> SerpScraperResponse:
        """IP Geolocation & proxy/hosting/mobile detection."""
        params = {**options}
        if ip:
            params["ip"] = ip
        return self._call("ip-lookup", params)

    def exchange_rate(
        self, from_currency: str = "USD", to_currency: Optional[str] = None, **options: Any
    ) -> SerpScraperResponse:
        """Fiat currency exchange rates (ECB)."""
        params = {"from": from_currency, **options}
        if to_currency:
            params["to"] = to_currency
        return self._call("exchange-rate", params)

    def crypto_price(
        self, symbols: str = "btc,eth", vs_currencies: str = "usd", **options: Any
    ) -> SerpScraperResponse:
        """Real-time crypto prices & 24h market stats."""
        params = {"symbols": symbols, "vs_currencies": vs_currencies, **options}
        return self._call("crypto-price", params)

    def domain_info(self, domain: str, **options: Any) -> SerpScraperResponse:
        """WHOIS, SSL certificate, DNS, and domain intelligence."""
        return self._call("domain-info", {"domain": domain, **options})

    # ── SEO Endpoints ─────────────────────────────────────────────────────────

    def seo_domain_overview(self, domain: str, **options: Any) -> SerpScraperResponse:
        """Domain SEO overview: rank, organic traffic, keyword count, technologies."""
        return self._call("seo/domain-overview", {"domain": domain, **options})

    def seo_backlinks(self, domain: str, **options: Any) -> SerpScraperResponse:
        """Backlink profile: domain rating, backlink count, referring domains."""
        return self._call("seo/backlinks", {"domain": domain, **options})

    def seo_keyword_metrics(self, keywords: Any, **options: Any) -> SerpScraperResponse:
        """Search volume, CPC and competition for up to 50 keywords (1 credit per 10)."""
        joined = ",".join(keywords) if isinstance(keywords, (list, tuple)) else keywords
        return self._call("seo/keywords/metrics", {"keywords": joined, **options})

    def seo_keyword_suggestions(self, keyword: str, **options: Any) -> SerpScraperResponse:
        """Autocomplete-based keyword suggestions."""
        return self._call("seo/keywords/suggestions", {"keyword": keyword, **options})

    # ── Proxy Endpoints ───────────────────────────────────────────────────────

    def proxy_session(self, country: Optional[str] = None, **options: Any) -> SerpScraperResponse:
        """Allocate a proxy session, optionally pinned to a country code."""
        params = dict(options)
        if country:
            params["country"] = country
        return self._call("proxy/session", params)

    def proxy_rotate(self, session_id: str, **options: Any) -> SerpScraperResponse:
        """Rotate the exit IP of an active proxy session."""
        return self._call("proxy/rotate", {"session_id": session_id, **options})

    def proxy_locations(self, **options: Any) -> SerpScraperResponse:
        """List supported proxy countries and regions."""
        return self._call("proxy/locations", dict(options))

    # ── Internal Network Handling ─────────────────────────────────────────────

    def _call(self, endpoint: str, params: Dict[str, Any]) -> SerpScraperResponse:
        call_params = {k: v for k, v in params.items() if v is not None}

        if self.config.country and "gl" not in call_params:
            call_params["gl"] = self.config.country
        if self.config.language and "hl" not in call_params:
            call_params["hl"] = self.config.language

        query_string = urllib.parse.urlencode(call_params)
        url = f"{self.config.base_url}/{endpoint.lstrip('/')}?{query_string}"

        req = urllib.request.Request(
            url,
            headers={
                "Accept": "application/json",
                "X-Api-Key": self.config.token,
                "User-Agent": self.config.user_agent,
            },
            method="GET",
        )

        try:
            with urllib.request.urlopen(req, timeout=self.config.timeout) as resp:
                status_code = resp.status
                body = resp.read().decode("utf-8")
        except urllib.error.HTTPError as e:
            status_code = e.code
            body = e.read().decode("utf-8")
        except urllib.error.URLError as e:
            raise NetworkException(f"Network connection error: {e.reason}") from e
        except Exception as e:
            raise NetworkException(f"Request failed: {str(e)}") from e

        return self._parse_response(body, status_code)

    def _parse_response(self, body: str, status_code: int) -> SerpScraperResponse:
        try:
            data = json.loads(body)
        except json.JSONDecodeError as e:
            raise SerpScraperException(f"Invalid JSON from API (HTTP {status_code}): {body[:100]}", status_code) from e

        if not isinstance(data, dict):
            data = {"data": data}

        message = str(data.get("message") or data.get("error") or f"HTTP {status_code}")

        if status_code in (401, 403):
            raise AuthException(message, status_code)
        if status_code == 429:
            raise RateLimitException(message, status_code)
        if status_code >= 400:
            raise SerpScraperException(message, status_code)

        return SerpScraperResponse(data)

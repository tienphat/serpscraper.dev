const { SerpScraperResponse } = require('./response');

class SerpScraperError extends Error {
  constructor(message, statusCode = 0) {
    super(message);
    this.name = 'SerpScraperError';
    this.statusCode = statusCode;
  }
}

class AuthError extends SerpScraperError {
  constructor(message, statusCode = 401) {
    super(message, statusCode);
    this.name = 'AuthError';
  }
}

class RateLimitError extends SerpScraperError {
  constructor(message, statusCode = 429) {
    super(message, statusCode);
    this.name = 'RateLimitError';
  }
}

class NetworkError extends SerpScraperError {
  constructor(message) {
    super(message, 0);
    this.name = 'NetworkError';
  }
}

class SerpScraperClient {
  /**
   * @param {string|{token: string, baseUrl?: string, timeout?: number, country?: string, language?: string}} config
   */
  constructor(config) {
    if (typeof config === 'string') {
      config = { token: config };
    }
    if (!config || !config.token || !config.token.trim()) {
      throw new Error('SerpScraper token must not be empty.');
    }

    this.token = config.token.trim();
    this.baseUrl = (config.baseUrl || 'https://serpscraper.dev/api/v1').replace(/\/+$/, '');
    this.timeout = Math.max(1000, (config.timeout || 30) * (config.timeout > 100 ? 1 : 1000));
    this.country = config.country ? config.country.toUpperCase() : null;
    this.language = config.language ? config.language.toLowerCase() : null;
    this.userAgent = config.userAgent || 'SerpScraperDev-Node/1.1 (+https://github.com/tienphat/serpscraper-php)';
  }

  // ── Search Endpoints ──────────────────────────────────────────────────────

  /** Google Search with organic rankings + Google AI Overview */
  async googleSearch(keyword, options = {}) {
    return this.webSearch(keyword, { engine: 'google', ...options });
  }

  /** Web Search results (Bing default or engine='google') */
  async webSearch(keyword, options = {}) {
    return this._call('webs-search', { keyword, ...options });
  }

  /** Standalone AI Overview synthesis */
  async aioSearch(keyword, options = {}) {
    return this._call('aio', { keyword, ...options });
  }

  /** Google Trends Real-Time trending searches by country */
  async trendsNow(options = {}) {
    return this._call('trends-now-search', options);
  }

  /** Google Trends Interest Over Time */
  async trendsInterest(keyword, options = {}) {
    return this._call('trends-interest-search', { keyword, ...options });
  }

  /** Image results with thumbnails & source URLs */
  async imageSearch(keyword, options = {}) {
    return this._call('images-search', { keyword, ...options });
  }

  /** Video results with views and duration */
  async videoSearch(keyword, options = {}) {
    return this._call('videos-search', { keyword, ...options });
  }

  /** News articles with source and date */
  async newsSearch(keyword, options = {}) {
    return this._call('news-search', { keyword, ...options });
  }

  /** Shopping products with price and merchant */
  async shoppingSearch(keyword, options = {}) {
    return this._call('shopping-search', { keyword, ...options });
  }

  /** Query auto-suggestions */
  async autocomplete(keyword, options = {}) {
    return this._call('autocomplete', { keyword, ...options });
  }

  /** Google Scholar papers and citations */
  async scholarSearch(keyword, options = {}) {
    return this._call('scholar-search', { keyword, ...options });
  }

  /** Local maps and places */
  async mapsSearch(keyword, options = {}) {
    return this._call('maps-search', { keyword, ...options });
  }

  /** Review-focused results */
  async reviewsSearch(keyword, options = {}) {
    return this._call('reviews-search', { keyword, ...options });
  }

  /** Unified endpoint (SerpApi format) */
  async search(keyword, options = {}) {
    return this._call('search', { keyword, ...options });
  }

  /** Extract content and metadata from webpage */
  async extractWebpage(url, includeHtml = false) {
    const params = { url };
    if (includeHtml) params.include_html = 'true';
    return this._call('webpage', params);
  }

  // ── Utility Endpoints ─────────────────────────────────────────────────────

  async ipLookup(ip = null, options = {}) {
    const params = { ...options };
    if (ip) params.ip = ip;
    return this._call('ip-lookup', params);
  }

  async exchangeRate(fromCurrency = 'USD', toCurrency = null, options = {}) {
    const params = { from: fromCurrency, ...options };
    if (toCurrency) params.to = toCurrency;
    return this._call('exchange-rate', params);
  }

  async cryptoPrice(symbols = 'btc,eth', vsCurrencies = 'usd', options = {}) {
    return this._call('crypto-price', { symbols, vs_currencies: vsCurrencies, ...options });
  }

  async domainInfo(domain, options = {}) {
    return this._call('domain-info', { domain, ...options });
  }

  // ── Internal HTTP Call ────────────────────────────────────────────────────

  async _call(endpoint, params = {}) {
    const query = new URLSearchParams();

    if (this.country && !params.gl) {
      query.set('gl', this.country);
    }
    if (this.language && !params.hl) {
      query.set('hl', this.language);
    }

    for (const [key, value] of Object.entries(params)) {
      if (value !== undefined && value !== null) {
        query.set(key, String(value));
      }
    }

    query.set('token', this.token);

    const url = `${this.baseUrl}/${endpoint.replace(/^\/+/, '')}?${query.toString()}`;

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.timeout);

    let res;
    let text;

    try {
      res = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'User-Agent': this.userAgent,
        },
        signal: controller.signal,
      });
      text = await res.text();
    } catch (err) {
      if (err.name === 'AbortError') {
        throw new NetworkError(`Request timed out after ${this.timeout}ms`);
      }
      throw new NetworkError(`Network request failed: ${err.message}`);
    } finally {
      clearTimeout(timeoutId);
    }

    let data;
    try {
      data = JSON.parse(text);
    } catch (err) {
      throw new SerpScraperError(`Invalid JSON from API (HTTP ${res.status}): ${text.slice(0, 100)}`, res.status);
    }

    const message = (data && (data.message || data.error)) || `HTTP ${res.status}`;

    if (res.status === 401 || res.status === 403) {
      throw new AuthError(message, res.status);
    }
    if (res.status === 429) {
      throw new RateLimitError(message, res.status);
    }
    if (res.status >= 400) {
      throw new SerpScraperError(message, res.status);
    }

    return new SerpScraperResponse(data);
  }
}

module.exports = {
  SerpScraperClient,
  SerpScraperError,
  AuthError,
  RateLimitError,
  NetworkError,
};

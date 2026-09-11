package serpscraper

import (
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"strings"
)

// Common error definitions.
var (
	ErrEmptyToken = errors.New("serpscraper: token must not be empty")
	ErrAuth       = errors.New("serpscraper: authentication failed (check API token)")
	ErrRateLimit  = errors.New("serpscraper: rate limit or credit quota exceeded")
)

// APIError represents an error returned by the SerpScraper API.
type APIError struct {
	StatusCode int
	Message    string
}

func (e *APIError) Error() string {
	return fmt.Sprintf("serpscraper API error (HTTP %d): %s", e.StatusCode, e.Message)
}

// Client represents the SerpScraper.Dev API client.
type Client struct {
	config     *Config
	httpClient *http.Client
}

// NewClient creates a client with the provided API token.
func NewClient(token string) (*Client, error) {
	if strings.TrimSpace(token) == "" {
		return nil, ErrEmptyToken
	}
	cfg := NewConfig(token)
	return NewClientWithConfig(cfg)
}

// NewClientWithConfig creates a client with customized Config.
func NewClientWithConfig(cfg *Config) (*Client, error) {
	if cfg == nil || strings.TrimSpace(cfg.Token) == "" {
		return nil, ErrEmptyToken
	}
	return &Client{
		config: cfg,
		httpClient: &http.Client{
			Timeout: cfg.Timeout,
		},
	}, nil
}

// ── Search Methods ────────────────────────────────────────────────────────────

// GoogleSearch performs a Google search with organic results and AI Overview extraction.
func (c *Client) GoogleSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	allOpts := append([]Option{WithEngine("google")}, opts...)
	return c.WebSearch(ctx, keyword, allOpts...)
}

// WebSearch performs a web search (Bing default or specified engine).
func (c *Client) WebSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "webs-search", params, opts...)
}

// AioSearch performs a standalone AI Overview search synthesis.
func (c *Client) AioSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "aio", params, opts...)
}

// TrendsNow fetches Google Real-Time trending searches.
func (c *Client) TrendsNow(ctx context.Context, opts ...Option) (*Response, error) {
	return c.call(ctx, "trends-now-search", map[string]string{}, opts...)
}

// TrendsInterest fetches Google Trends Interest Over Time data.
func (c *Client) TrendsInterest(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "trends-interest-search", params, opts...)
}

// ImageSearch searches for images with thumbnail metadata.
func (c *Client) ImageSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "images-search", params, opts...)
}

// VideoSearch searches for videos with duration and publisher info.
func (c *Client) VideoSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "videos-search", params, opts...)
}

// NewsSearch searches for live news articles.
func (c *Client) NewsSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "news-search", params, opts...)
}

// ShoppingSearch searches for e-commerce products and pricing.
func (c *Client) ShoppingSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "shopping-search", params, opts...)
}

// Autocomplete fetches query search suggestions.
func (c *Client) Autocomplete(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "autocomplete", params, opts...)
}

// ScholarSearch searches Google Scholar research publications.
func (c *Client) ScholarSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "scholar-search", params, opts...)
}

// MapsSearch searches local places, businesses, and coordinates.
func (c *Client) MapsSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "maps-search", params, opts...)
}

// ReviewsSearch searches for user reviews and ratings.
func (c *Client) ReviewsSearch(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "reviews-search", params, opts...)
}

// Search calls the Unified / SerpApi compatible endpoint.
func (c *Client) Search(ctx context.Context, keyword string, opts ...Option) (*Response, error) {
	params := map[string]string{"keyword": keyword}
	return c.call(ctx, "search", params, opts...)
}

// ExtractWebpage extracts full text content, metadata, and links from any URL.
func (c *Client) ExtractWebpage(ctx context.Context, targetURL string, includeHTML bool) (*Response, error) {
	params := map[string]string{"url": targetURL}
	if includeHTML {
		params["include_html"] = "true"
	}
	return c.call(ctx, "webpage", params)
}

// IPLookup performs IP geolocation and proxy detection.
func (c *Client) IPLookup(ctx context.Context, ip string, opts ...Option) (*Response, error) {
	params := map[string]string{}
	if ip != "" {
		params["ip"] = ip
	}
	return c.call(ctx, "ip-lookup", params, opts...)
}

// ExchangeRate retrieves fiat currency exchange rates (ECB).
func (c *Client) ExchangeRate(ctx context.Context, fromCurrency, toCurrency string, opts ...Option) (*Response, error) {
	params := map[string]string{"from": fromCurrency}
	if toCurrency != "" {
		params["to"] = toCurrency
	}
	return c.call(ctx, "exchange-rate", params, opts...)
}

// CryptoPrice retrieves real-time crypto prices & 24h market statistics.
func (c *Client) CryptoPrice(ctx context.Context, symbols, vsCurrencies string, opts ...Option) (*Response, error) {
	params := map[string]string{"symbols": symbols, "vs_currencies": vsCurrencies}
	return c.call(ctx, "crypto-price", params, opts...)
}

// DomainInfo retrieves WHOIS, SSL cert, DNS, and domain intelligence.
func (c *Client) DomainInfo(ctx context.Context, domain string, opts ...Option) (*Response, error) {
	params := map[string]string{"domain": domain}
	return c.call(ctx, "domain-info", params, opts...)
}

// ── Internal Dispatcher ───────────────────────────────────────────────────────

func (c *Client) call(ctx context.Context, endpoint string, baseParams map[string]string, opts ...Option) (*Response, error) {
	m := make(map[string]string)
	for k, v := range baseParams {
		m[k] = v
	}

	for _, opt := range opts {
		if opt != nil {
			opt(m)
		}
	}

	if c.config.Country != "" && m["gl"] == "" {
		m["gl"] = c.config.Country
	}
	if c.config.Language != "" && m["hl"] == "" {
		m["hl"] = c.config.Language
	}

	m["token"] = c.config.Token

	vals := url.Values{}
	for k, v := range m {
		if v != "" {
			vals.Set(k, v)
		}
	}

	reqURL := fmt.Sprintf("%s/%s?%s", c.config.BaseURL, strings.TrimLeft(endpoint, "/"), vals.Encode())

	req, err := http.NewRequestWithContext(ctx, http.MethodGet, reqURL, nil)
	if err != nil {
		return nil, fmt.Errorf("serpscraper: failed to build request: %w", err)
	}

	req.Header.Set("Accept", "application/json")
	req.Header.Set("User-Agent", c.config.UserAgent)

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return nil, fmt.Errorf("serpscraper: network error: %w", err)
	}
	defer resp.Body.Close()

	bodyBytes, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("serpscraper: failed to read response body: %w", err)
	}

	if resp.StatusCode == http.StatusUnauthorized || resp.StatusCode == http.StatusForbidden {
		return nil, ErrAuth
	}
	if resp.StatusCode == http.StatusTooManyRequests {
		return nil, ErrRateLimit
	}
	if resp.StatusCode >= 400 {
		var errData map[string]interface{}
		_ = json.Unmarshal(bodyBytes, &errData)
		msg := fmt.Sprintf("HTTP %d", resp.StatusCode)
		if m, ok := errData["message"].(string); ok && m != "" {
			msg = m
		} else if e, ok := errData["error"].(string); ok && e != "" {
			msg = e
		}
		return nil, &APIError{StatusCode: resp.StatusCode, Message: msg}
	}

	var parsed Response
	if err := json.Unmarshal(bodyBytes, &parsed); err != nil {
		return nil, fmt.Errorf("serpscraper: invalid JSON from API: %w", err)
	}
	parsed.RawBytes = bodyBytes

	return &parsed, nil
}

package serpscraper

import (
	"strings"
	"time"
)

const (
	DefaultBaseURL   = "https://serpscraper.dev/api/v1"
	DefaultTimeout   = 30 * time.Second
	DefaultUserAgent = "SerpScraperDev-Go/1.1 (+https://github.com/tienphat/serpscraper-php)"
)

// Config holds client configurations.
type Config struct {
	Token     string
	BaseURL   string
	Timeout   time.Duration
	Country   string
	Language  string
	UserAgent string
}

// NewConfig creates a new configuration with default settings.
func NewConfig(token string) *Config {
	return &Config{
		Token:     strings.TrimSpace(token),
		BaseURL:   DefaultBaseURL,
		Timeout:   DefaultTimeout,
		UserAgent: DefaultUserAgent,
	}
}

// WithCountry sets the default country code for all requests.
func (c *Config) WithCountry(gl string) *Config {
	c.Country = strings.ToUpper(gl)
	return c
}

// WithLanguage sets the default language code for all requests.
func (c *Config) WithLanguage(hl string) *Config {
	c.Language = strings.ToLower(hl)
	return c
}

// WithTimeout sets the request timeout.
func (c *Config) WithTimeout(d time.Duration) *Config {
	if d > 0 {
		c.Timeout = d
	}
	return c
}

// WithBaseURL overrides the API base URL.
func (c *Config) WithBaseURL(url string) *Config {
	c.BaseURL = strings.TrimRight(url, "/")
	return c
}

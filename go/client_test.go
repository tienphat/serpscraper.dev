package serpscraper

import (
	"context"
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"testing"
	"time"
)

func TestConfigDefaults(t *testing.T) {
	cfg := NewConfig("test-token").WithCountry("US").WithLanguage("en").WithTimeout(15 * time.Second)
	if cfg.Token != "test-token" {
		t.Errorf("expected token test-token, got %s", cfg.Token)
	}
	if cfg.Country != "US" {
		t.Errorf("expected country US, got %s", cfg.Country)
	}
	if cfg.Language != "en" {
		t.Errorf("expected language en, got %s", cfg.Language)
	}
	if cfg.BaseURL != DefaultBaseURL {
		t.Errorf("expected default base URL, got %s", cfg.BaseURL)
	}
}

func TestEmptyTokenError(t *testing.T) {
	_, err := NewClient("")
	if err != ErrEmptyToken {
		t.Errorf("expected ErrEmptyToken, got %v", err)
	}
}

func TestGoogleSearchWithMockServer(t *testing.T) {
	mockPayload := map[string]interface{}{
		"request": map[string]interface{}{"keyword": "openai", "engine": "google"},
		"data": []map[string]interface{}{
			{"position": 1, "title": "OpenAI Home", "link": "https://openai.com"},
		},
		"ai_overview": map[string]interface{}{
			"summary": "OpenAI overview summary text",
			"sources": []map[string]interface{}{
				{"title": "Wiki", "link": "https://en.wikipedia.org/wiki/OpenAI"},
			},
		},
		"in_seconds": 1.25,
	}

	ts := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Query().Get("engine") != "google" {
			t.Errorf("expected engine=google in query, got %s", r.URL.Query().Get("engine"))
		}
		if r.Header.Get("X-Api-Key") != "secret" {
			t.Errorf("expected X-Api-Key header to be secret, got %q", r.Header.Get("X-Api-Key"))
		}
		if r.URL.Query().Get("token") != "" {
			t.Errorf("token must not be sent in the query string")
		}
		w.Header().Set("Content-Type", "application/json")
		_ = json.NewEncoder(w).Encode(mockPayload)
	}))
	defer ts.Close()

	cfg := NewConfig("secret").WithBaseURL(ts.URL)
	client, err := NewClientWithConfig(cfg)
	if err != nil {
		t.Fatalf("failed to create client: %v", err)
	}

	resp, err := client.GoogleSearch(context.Background(), "openai", WithCountry("US"))
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}

	if resp.Length() != 1 {
		t.Errorf("expected 1 item, got %d", resp.Length())
	}
	if resp.First() == nil || resp.First().Title != "OpenAI Home" {
		t.Errorf("unexpected first item title: %v", resp.First())
	}
	if resp.Summary() != "OpenAI overview summary text" {
		t.Errorf("unexpected summary: %s", resp.Summary())
	}
	if len(resp.Sources()) != 1 {
		t.Errorf("expected 1 source, got %d", len(resp.Sources()))
	}
}

func TestSEOAndProxyEndpoints(t *testing.T) {
	var seen []*http.Request
	ts := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		seen = append(seen, r.Clone(context.Background()))
		w.Header().Set("Content-Type", "application/json")
		_, _ = w.Write([]byte(`{"data":[],"in_seconds":0}`))
	}))
	defer ts.Close()

	client, err := NewClientWithConfig(NewConfig("tok").WithBaseURL(ts.URL))
	if err != nil {
		t.Fatalf("failed to create client: %v", err)
	}
	ctx := context.Background()

	_, _ = client.SEODomainOverview(ctx, "apple.com")
	_, _ = client.SEOBacklinks(ctx, "github.com")
	_, _ = client.SEOKeywordMetrics(ctx, []string{"serp api", "rank tracker"})
	_, _ = client.SEOKeywordSuggestions(ctx, "laravel")
	_, _ = client.ProxySession(ctx, "US")
	_, _ = client.ProxySession(ctx, "")
	_, _ = client.ProxyRotate(ctx, "abc123")
	_, _ = client.ProxyLocations(ctx)

	want := []struct{ path, key, val string }{
		{"/seo/domain-overview", "domain", "apple.com"},
		{"/seo/backlinks", "domain", "github.com"},
		{"/seo/keywords/metrics", "keywords", "serp api,rank tracker"},
		{"/seo/keywords/suggestions", "keyword", "laravel"},
		{"/proxy/session", "country", "US"},
		{"/proxy/session", "country", ""},
		{"/proxy/rotate", "session_id", "abc123"},
		{"/proxy/locations", "token", ""},
	}
	if len(seen) != len(want) {
		t.Fatalf("expected %d requests, got %d", len(want), len(seen))
	}
	for i, w := range want {
		if seen[i].URL.Path != w.path {
			t.Errorf("request %d: path %s, want %s", i, seen[i].URL.Path, w.path)
		}
		if got := seen[i].URL.Query().Get(w.key); got != w.val {
			t.Errorf("request %d: %s=%q, want %q", i, w.key, got, w.val)
		}
	}
}

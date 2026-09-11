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
		if r.URL.Query().Get("token") != "secret" {
			t.Errorf("expected token=secret in query, got %s", r.URL.Query().Get("token"))
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

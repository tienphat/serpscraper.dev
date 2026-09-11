package serpscraper

import (
	"encoding/json"
)

// Response wraps the raw SerpScraper API response.
type Response struct {
	Request    map[string]interface{} `json:"request"`
	Data       json.RawMessage        `json:"data"`
	InSeconds  float64                `json:"in_seconds"`
	AiOverview *AiOverview            `json:"ai_overview,omitempty"`
	RawBytes   []byte                 `json:"-"`
}

// Items unmarshals and returns the list of result items.
func (r *Response) Items() []ResultItem {
	if len(r.Data) == 0 {
		return nil
	}

	var list []ResultItem
	if err := json.Unmarshal(r.Data, &list); err == nil {
		return list
	}

	var obj map[string]json.RawMessage
	if err := json.Unmarshal(r.Data, &obj); err == nil {
		keys := []string{
			"items", "products", "images", "videos", "news", "scholar",
			"places", "reviews", "suggestions", "interest_over_time", "trending_searches",
		}
		for _, k := range keys {
			if raw, ok := obj[k]; ok {
				var nested []ResultItem
				if err := json.Unmarshal(raw, &nested); err == nil {
					return nested
				}
			}
		}
	}

	return nil
}

// First returns the first result item or nil if empty.
func (r *Response) First() *ResultItem {
	items := r.Items()
	if len(items) > 0 {
		return &items[0]
	}
	return nil
}

// Pluck returns an array of string values for the given field name.
func (r *Response) Pluck(field string) []string {
	items := r.Items()
	var res []string
	for _, it := range items {
		switch field {
		case "link":
			if it.Link != "" {
				res = append(res, it.Link)
			}
		case "title":
			if it.Title != "" {
				res = append(res, it.Title)
			}
		case "keyword":
			if it.Keyword != "" {
				res = append(res, it.Keyword)
			}
		}
	}
	return res
}

// Summary returns the AI Overview summary text if available.
func (r *Response) Summary() string {
	if r.AiOverview != nil {
		return r.AiOverview.Summary
	}
	return ""
}

// Sources returns the AI Overview source citations if available.
func (r *Response) Sources() []AiSource {
	if r.AiOverview != nil {
		return r.AiOverview.Sources
	}
	return nil
}

// Timeline parses Google Trends interest over time data points.
func (r *Response) Timeline() []TimelinePoint {
	if len(r.Data) == 0 {
		return nil
	}
	var obj map[string]json.RawMessage
	if err := json.Unmarshal(r.Data, &obj); err == nil {
		for _, key := range []string{"interest_over_time", "timeline_data"} {
			if raw, ok := obj[key]; ok {
				var points []TimelinePoint
				if err := json.Unmarshal(raw, &points); err == nil {
					return points
				}
			}
		}
	}
	return nil
}

// Latency returns response execution time in seconds.
func (r *Response) Latency() float64 {
	return r.InSeconds
}

// Length returns the count of items in the response.
func (r *Response) Length() int {
	return len(r.Items())
}

// IsEmpty returns true if there are no result items.
func (r *Response) IsEmpty() bool {
	return r.Length() == 0
}

// Raw returns the full raw JSON response bytes.
func (r *Response) Raw() []byte {
	return r.RawBytes
}

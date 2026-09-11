package serpscraper

// Option represents a functional option or parameter for API queries.
type Option func(map[string]string)

// WithCountry sets the country code (gl), e.g. "US", "VN", "GB".
func WithCountry(gl string) Option {
	return func(m map[string]string) {
		m["gl"] = gl
	}
}

// WithLanguage sets the language code (hl), e.g. "en", "vi".
func WithLanguage(hl string) Option {
	return func(m map[string]string) {
		m["hl"] = hl
	}
}

// WithPage sets the pagination page number.
func WithPage(page string) Option {
	return func(m map[string]string) {
		m["page"] = page
	}
}

// WithSize sets the result size limit (max 100).
func WithSize(size string) Option {
	return func(m map[string]string) {
		m["size"] = size
	}
}

// WithEngine sets the search engine, e.g. "google", "bing", "curl".
func WithEngine(engine string) Option {
	return func(m map[string]string) {
		m["engine"] = engine
	}
}

// WithTime sets the time filter (e.g. "today 12-m", "d", "w", "m", "y").
func WithTime(t string) Option {
	return func(m map[string]string) {
		m["time"] = t
	}
}

// WithParam sets any custom arbitrary query parameter.
func WithParam(key, value string) Option {
	return func(m map[string]string) {
		m[key] = value
	}
}

// ResultItem represents a standard organic search result or trending item.
type ResultItem struct {
	Position    int    `json:"position,omitempty"`
	Title       string `json:"title,omitempty"`
	Link        string `json:"link,omitempty"`
	Description string `json:"description,omitempty"`
	Snippet     string `json:"snippet,omitempty"`
	CateSlug    string `json:"cate_slug,omitempty"`
	Keyword     string `json:"keyword,omitempty"`
	Traffic     string `json:"traffic,omitempty"`
	Source      string `json:"source,omitempty"`
	Date        string `json:"date,omitempty"`
	Price       string `json:"price,omitempty"`
	ImageUrl    string `json:"image_url,omitempty"`
}

// AiSource represents an attribution citation in an AI Overview.
type AiSource struct {
	Title       string `json:"title"`
	Site        string `json:"site,omitempty"`
	Link        string `json:"link"`
	Snippet     string `json:"snippet,omitempty"`
	Description string `json:"description,omitempty"`
}

// AiOverview represents the AI Overview synthesis block.
type AiOverview struct {
	Summary       string     `json:"summary"`
	SummaryHtml   string     `json:"summary_html,omitempty"`
	Sources       []AiSource `json:"sources,omitempty"`
	SearchQueries []string   `json:"search_queries,omitempty"`
}

// TimelinePoint represents a single data point in Google Trends interest over time.
type TimelinePoint struct {
	Date  string `json:"date"`
	Value int    `json:"value"`
}

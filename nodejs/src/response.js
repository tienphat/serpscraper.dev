/**
 * SerpScraper response wrapper.
 */
class SerpScraperResponse {
  constructor(raw) {
    this.raw = raw || {};
  }

  get data() {
    const val = this.raw.data || [];
    if (val && typeof val === 'object' && !Array.isArray(val)) {
      const keys = [
        'items', 'products', 'images', 'videos', 'news', 'scholar',
        'places', 'reviews', 'suggestions', 'interest_over_time', 'trending_searches'
      ];
      for (const k of keys) {
        if (Array.isArray(val[k])) {
          return val[k];
        }
      }
      return [];
    }
    return Array.isArray(val) ? val : [];
  }

  get first() {
    const items = this.data;
    return items.length > 0 ? items[0] : null;
  }

  pluck(field) {
    return this.data
      .map(item => item && item[field])
      .filter(v => v !== undefined && v !== null);
  }

  get aiOverview() {
    return this.raw.ai_overview || null;
  }

  get summary() {
    return (this.raw.ai_overview && this.raw.ai_overview.summary) || null;
  }

  get sources() {
    return (this.raw.ai_overview && this.raw.ai_overview.sources) || [];
  }

  get timelineData() {
    const d = this.raw.data;
    if (d && typeof d === 'object') {
      if (Array.isArray(d.interest_over_time)) return d.interest_over_time;
      if (Array.isArray(d.timeline_data)) return d.timeline_data;
    }
    return [];
  }

  get request() {
    return this.raw.request || {};
  }

  get inSeconds() {
    return Number(this.raw.in_seconds || 0);
  }

  get length() {
    return this.data.length;
  }

  get isEmpty() {
    return this.data.length === 0;
  }

  [Symbol.iterator]() {
    return this.data[Symbol.iterator]();
  }

  toJSON() {
    return this.raw;
  }
}

module.exports = { SerpScraperResponse };

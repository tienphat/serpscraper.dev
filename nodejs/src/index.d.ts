export interface SerpScraperConfigOptions {
  token: string;
  baseUrl?: string;
  timeout?: number;
  country?: string;
  language?: string;
  userAgent?: string;
}

export interface SearchOptions {
  gl?: string;
  hl?: string;
  page?: number;
  size?: number;
  engine?: string;
  time?: string;
  ai_overview?: number | boolean;
  [key: string]: any;
}

export class SerpScraperResponse {
  readonly raw: Record<string, any>;
  readonly data: Array<Record<string, any>>;
  readonly first: Record<string, any> | null;
  readonly aiOverview: Record<string, any> | null;
  readonly summary: string | null;
  readonly sources: Array<Record<string, any>>;
  readonly timelineData: Array<Record<string, any>>;
  readonly request: Record<string, any>;
  readonly inSeconds: number;
  readonly length: number;
  readonly isEmpty: boolean;

  pluck(field: string): any[];
  toJSON(): Record<string, any>;
  [Symbol.iterator](): Iterator<Record<string, any>>;
}

export class SerpScraperError extends Error {
  statusCode: number;
}
export class AuthError extends SerpScraperError {}
export class RateLimitError extends SerpScraperError {}
export class NetworkError extends SerpScraperError {}

export class SerpScraperClient {
  constructor(config: string | SerpScraperConfigOptions);

  googleSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  webSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  aioSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  trendsNow(options?: SearchOptions): Promise<SerpScraperResponse>;
  trendsInterest(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  imageSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  videoSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  newsSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  shoppingSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  autocomplete(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  scholarSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  mapsSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  reviewsSearch(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  search(keyword: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  extractWebpage(url: string, includeHtml?: boolean): Promise<SerpScraperResponse>;
  ipLookup(ip?: string | null, options?: SearchOptions): Promise<SerpScraperResponse>;
  exchangeRate(fromCurrency?: string, toCurrency?: string | null, options?: SearchOptions): Promise<SerpScraperResponse>;
  cryptoPrice(symbols?: string, vsCurrencies?: string, options?: SearchOptions): Promise<SerpScraperResponse>;
  domainInfo(domain: string, options?: SearchOptions): Promise<SerpScraperResponse>;
}

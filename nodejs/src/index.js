const { SerpScraperClient, SerpScraperError, AuthError, RateLimitError, NetworkError } = require('./client');
const { SerpScraperResponse } = require('./response');

module.exports = {
  SerpScraperClient,
  SerpScraperResponse,
  SerpScraperError,
  AuthError,
  RateLimitError,
  NetworkError,
};

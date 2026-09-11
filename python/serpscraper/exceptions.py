"""SerpScraper exceptions hierarchy."""

class SerpScraperException(Exception):
    """Base exception for all SerpScraper API errors."""
    def __init__(self, message: str, status_code: int = 0):
        super().__init__(message)
        self.message = message
        self.status_code = status_code


class AuthException(SerpScraperException):
    """Raised when authentication fails (HTTP 401 or 403)."""
    pass


class RateLimitException(SerpScraperException):
    """Raised when credit quota is exceeded or rate limit hit (HTTP 429)."""
    pass


class NetworkException(SerpScraperException):
    """Raised when a network/connection error occurs."""
    pass

"""SerpScraper Python SDK — official library for SerpScraper.Dev API."""

from .client import SerpScraperClient
from .config import SerpScraperConfig
from .exceptions import (
    AuthException,
    NetworkException,
    RateLimitException,
    SerpScraperException,
)
from .response import SerpScraperResponse

__version__ = "1.1.0"
__all__ = [
    "SerpScraperClient",
    "SerpScraperConfig",
    "SerpScraperResponse",
    "SerpScraperException",
    "AuthException",
    "RateLimitException",
    "NetworkException",
]

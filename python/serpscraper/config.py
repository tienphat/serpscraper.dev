"""SerpScraper client configuration."""

from typing import Optional


class SerpScraperConfig:
    DEFAULT_BASE_URL = "https://serpscraper.dev/api/v1"
    DEFAULT_TIMEOUT = 30
    DEFAULT_USER_AGENT = "SerpScraperDev-Python/1.1 (+https://github.com/tienphat/serpscraper-php)"

    def __init__(
        self,
        token: str,
        base_url: str = DEFAULT_BASE_URL,
        timeout: int = DEFAULT_TIMEOUT,
        country: Optional[str] = None,
        language: Optional[str] = None,
        user_agent: str = DEFAULT_USER_AGENT,
    ):
        token = token.strip()
        if not token:
            raise ValueError("SerpScraper token must not be empty.")

        self.token = token
        self.base_url = base_url.rstrip("/")
        self.timeout = max(1, int(timeout))
        self.country = country.upper() if country else None
        self.language = language.lower() if language else None
        self.user_agent = user_agent

    def with_country(self, country: str) -> "SerpScraperConfig":
        return SerpScraperConfig(
            token=self.token,
            base_url=self.base_url,
            timeout=self.timeout,
            country=country,
            language=self.language,
            user_agent=self.user_agent,
        )

    def with_language(self, language: str) -> "SerpScraperConfig":
        return SerpScraperConfig(
            token=self.token,
            base_url=self.base_url,
            timeout=self.timeout,
            country=self.country,
            language=language,
            user_agent=self.user_agent,
        )

    def with_timeout(self, timeout: int) -> "SerpScraperConfig":
        return SerpScraperConfig(
            token=self.token,
            base_url=self.base_url,
            timeout=timeout,
            country=self.country,
            language=self.language,
            user_agent=self.user_agent,
        )

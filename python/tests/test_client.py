import unittest
from unittest.mock import patch, MagicMock
from io import BytesIO
import json
import urllib.error

from serpscraper import SerpScraperClient, SerpScraperConfig, SerpScraperResponse
from serpscraper.exceptions import AuthException, RateLimitException, SerpScraperException


class TestSerpScraperPython(unittest.TestCase):
    def test_config_initialization(self):
        config = SerpScraperConfig("test-token", country="US", language="en")
        self.assertEqual(config.token, "test-token")
        self.assertEqual(config.country, "US")
        self.assertEqual(config.language, "en")
        self.assertEqual(config.base_url, "https://serpscraper.dev/api/v1")

    def test_config_empty_token_raises(self):
        with self.assertRaises(ValueError):
            SerpScraperConfig("")

    def test_response_accessors(self):
        raw = {
            "data": [
                {"position": 1, "title": "Example", "link": "https://example.com"}
            ],
            "ai_overview": {
                "summary": "This is an AI summary.",
                "sources": [{"title": "Wiki", "link": "https://wiki.org"}]
            },
            "in_seconds": 0.45
        }
        res = SerpScraperResponse(raw)
        self.assertEqual(len(res), 1)
        self.assertEqual(res.first["title"], "Example")
        self.assertEqual(res.pluck("link"), ["https://example.com"])
        self.assertEqual(res.summary, "This is an AI summary.")
        self.assertEqual(len(res.sources), 1)
        self.assertEqual(res.in_seconds, 0.45)
        self.assertFalse(res.is_empty)

    @patch("urllib.request.urlopen")
    def test_client_google_search(self, mock_urlopen):
        mock_response = MagicMock()
        mock_response.status = 200
        mock_response.read.return_value = json.dumps({
            "request": {"keyword": "test", "engine": "google"},
            "data": [{"title": "Google Result", "link": "https://google.com"}],
            "in_seconds": 1.2
        }).encode("utf-8")
        mock_response.__enter__.return_value = mock_response
        mock_urlopen.return_value = mock_response

        client = SerpScraperClient("my-secret-key")
        res = client.google_search("test", gl="US")

        self.assertEqual(len(res), 1)
        self.assertEqual(res.first["title"], "Google Result")

        req = mock_urlopen.call_args[0][0]
        self.assertIn("engine=google", req.full_url)
        self.assertIn("keyword=test", req.full_url)
        self.assertIn("token=my-secret-key", req.full_url)

    @patch("urllib.request.urlopen")
    def test_client_auth_error_raises_exception(self, mock_urlopen):
        mock_urlopen.side_effect = urllib.error.HTTPError(
            url="http://test",
            code=401,
            msg="Unauthorized",
            hdrs={},
            fp=BytesIO(b'{"message": "Invalid token key!"}')
        )

        client = SerpScraperClient("bad-token")
        with self.assertRaises(AuthException):
            client.web_search("test")


if __name__ == "__main__":
    unittest.main()

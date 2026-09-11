"""SerpScraper response wrapper."""

import json
from typing import Any, Dict, Iterator, List, Optional


class SerpScraperResponse:
    def __init__(self, raw: Dict[str, Any]):
        self.raw = raw or {}

    @property
    def data(self) -> List[Dict[str, Any]]:
        val = self.raw.get("data", [])
        if isinstance(val, dict):
            for key in [
                "items", "products", "images", "videos", "news", "scholar",
                "places", "reviews", "suggestions", "interest_over_time", "trending_searches"
            ]:
                if key in val and isinstance(val[key], list):
                    return val[key]
            return []
        return val if isinstance(val, list) else []

    @property
    def first(self) -> Optional[Dict[str, Any]]:
        items = self.data
        return items[0] if items else None

    def pluck(self, field: str) -> List[Any]:
        return [item[field] for item in self.data if isinstance(item, dict) and field in item]

    @property
    def ai_overview(self) -> Optional[Dict[str, Any]]:
        return self.raw.get("ai_overview")

    @property
    def summary(self) -> Optional[str]:
        aio = self.ai_overview
        return aio.get("summary") if isinstance(aio, dict) else None

    @property
    def sources(self) -> List[Dict[str, Any]]:
        aio = self.ai_overview
        return aio.get("sources", []) if isinstance(aio, dict) else []

    @property
    def timeline_data(self) -> List[Dict[str, Any]]:
        data = self.raw.get("data", {})
        if isinstance(data, dict):
            if "interest_over_time" in data and isinstance(data["interest_over_time"], list):
                return data["interest_over_time"]
            if "timeline_data" in data and isinstance(data["timeline_data"], list):
                return data["timeline_data"]
        return []

    @property
    def request(self) -> Dict[str, Any]:
        return self.raw.get("request", {})

    @property
    def in_seconds(self) -> float:
        return float(self.raw.get("in_seconds", 0.0))

    @property
    def is_empty(self) -> bool:
        return len(self.data) == 0

    def to_dict(self) -> Dict[str, Any]:
        return self.raw

    def to_json(self, indent: int = 2) -> str:
        return json.dumps(self.raw, indent=indent, ensure_ascii=False)

    def __len__(self) -> int:
        return len(self.data)

    def __iter__(self) -> Iterator[Dict[str, Any]]:
        return iter(self.data)

    def __getitem__(self, index: int) -> Dict[str, Any]:
        return self.data[index]

    def __repr__(self) -> str:
        return f"<SerpScraperResponse items={len(self.data)} latency={self.in_seconds:.3f}s>"

from setuptools import setup, find_packages

setup(
    name="serpscraper",
    version="1.1.0",
    description="Official Python client for SerpScraper.Dev API (Google & Bing Search, AI Overviews, Google Trends).",
    author="SerpScraper.Dev",
    author_email="domaininfo2110@gmail.com",
    url="https://serpscraper.dev",
    packages=find_packages(),
    classifiers=[
        "Programming Language :: Python :: 3",
        "License :: OSI Approved :: MIT License",
        "Operating System :: OS Independent",
    ],
    python_requires=">=3.8",
)

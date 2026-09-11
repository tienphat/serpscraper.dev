<?php

declare(strict_types=1);

// Backward compatibility layer for SerpApiOrg names
if (!class_exists('SerpApiOrg\SerpApiClient', false)) {
    class_alias(SerpScraper\SerpScraperClient::class, 'SerpApiOrg\SerpApiClient');
}

if (!class_exists('SerpApiOrg\SerpApiConfig', false)) {
    class_alias(SerpScraper\SerpScraperConfig::class, 'SerpApiOrg\SerpApiConfig');
}

if (!class_exists('SerpApiOrg\SerpApiResponse', false)) {
    class_alias(SerpScraper\SerpScraperResponse::class, 'SerpApiOrg\SerpApiResponse');
}

if (!interface_exists('SerpApiOrg\Contracts\SerpApiClientInterface', false)) {
    class_alias(SerpScraper\Contracts\SerpScraperClientInterface::class, 'SerpApiOrg\Contracts\SerpApiClientInterface');
}

if (!class_exists('SerpApiOrg\Exceptions\SerpApiException', false)) {
    class_alias(SerpScraper\Exceptions\SerpScraperException::class, 'SerpApiOrg\Exceptions\SerpApiException');
}

if (!class_exists('SerpApiOrg\Exceptions\AuthException', false)) {
    class_alias(SerpScraper\Exceptions\AuthException::class, 'SerpApiOrg\Exceptions\AuthException');
}

if (!class_exists('SerpApiOrg\Exceptions\RateLimitException', false)) {
    class_alias(SerpScraper\Exceptions\RateLimitException::class, 'SerpApiOrg\Exceptions\RateLimitException');
}

if (!class_exists('SerpApiOrg\Exceptions\NetworkException', false)) {
    class_alias(SerpScraper\Exceptions\NetworkException::class, 'SerpApiOrg\Exceptions\NetworkException');
}

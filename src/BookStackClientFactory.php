<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Factory for creating BookStack clients.
 *
 * @author Camille Islasse
 */
class BookStackClientFactory
{
    /**
     * Create a BookStack client with default configuration.
     */
    public static function create(string $baseUrl, string $apiKey, string $apiSecret): BookStackClient
    {
        $httpClient = HttpClient::create([
            'timeout' => 30,
            'max_redirects' => 3,
            'headers' => [
                'User-Agent' => 'BookStack-PHP-Client/1.0',
            ],
        ]);

        return new BookStackClient($httpClient, $baseUrl, $apiKey, $apiSecret);
    }

    /**
     * Create a BookStack client with custom HTTP client.
     */
    public static function createWithHttpClient(
        HttpClientInterface $httpClient,
        string $baseUrl,
        string $apiKey,
        string $apiSecret,
    ): BookStackClient {
        return new BookStackClient($httpClient, $baseUrl, $apiKey, $apiSecret);
    }

    /**
     * Create a BookStack client with custom options.
     *
     * @param array<string, mixed> $options
     */
    public static function createWithOptions(
        string $baseUrl,
        string $apiKey,
        string $apiSecret,
        array $options = [],
    ): BookStackClient {
        $defaultOptions = [
            'timeout' => 30,
            'max_redirects' => 3,
            'headers' => [
                'User-Agent' => 'BookStack-PHP-Client/1.0',
            ],
        ];

        $httpClient = HttpClient::create(array_merge($defaultOptions, $options));

        return new BookStackClient($httpClient, $baseUrl, $apiKey, $apiSecret);
    }
}

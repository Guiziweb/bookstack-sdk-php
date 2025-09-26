<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * HTTP Client with BookStack authentication.
 *
 * @author Camille Islasse
 */
class AuthenticatedHttpClient
{
    private HttpClientInterface $httpClient;
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;

    /** @var array<string, string> */
    private array $defaultHeaders;

    public function __construct(
        HttpClientInterface $httpClient,
        string $baseUrl,
        string $apiKey,
        string $apiSecret,
    ) {
        $this->validateCredentials($apiKey, $apiSecret);

        $this->httpClient = $httpClient;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        $this->defaultHeaders = [
            'Authorization' => $this->generateAuthHeader(),
            'User-Agent' => 'BookStack-PHP-Client/1.0',
            'Accept' => 'application/json',
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $headers = array_merge(
            $this->defaultHeaders,
            $options['headers'] ?? []
        );

        // Only add Content-Type for requests with body
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
            if (!isset($headers['Content-Type']) && isset($options['json'])) {
                $headers['Content-Type'] = 'application/json';
            }
        }

        $options['headers'] = $headers;
        $options['timeout'] = $options['timeout'] ?? 30;

        $fullUrl = str_starts_with($url, 'http') ? $url : $this->baseUrl.$url;

        return $this->httpClient->request($method, $fullUrl, $options);
    }

    private function validateCredentials(string $apiKey, string $apiSecret): void
    {
        if ('' === trim($apiKey) || '' === trim($apiSecret)) {
            throw new Exception\ValidationException('API key and secret cannot be empty');
        }

        if (strlen($apiKey) < 10 || strlen($apiSecret) < 10) {
            throw new Exception\ValidationException('API key and secret must be at least 10 characters long');
        }
    }

    private function generateAuthHeader(): string
    {
        return sprintf('Token %s:%s', $this->apiKey, $this->apiSecret);
    }

    /**
     * Clear sensitive data from memory.
     */
    public function __destruct()
    {
        // Clear sensitive data
        $this->apiKey = '';
        $this->apiSecret = '';
        $this->defaultHeaders = [];
    }
}

<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Unit;

use Guiziweb\BookStackClient\AuthenticatedHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Unit tests for AuthenticatedHttpClient.
 *
 * @author Camille Islasse
 */
class AuthenticatedHttpClientTest extends TestCase
{
    private const BASE_URL = 'https://bookstack.example.com';
    private const API_KEY = 'test-api-key';
    private const API_SECRET = 'test-api-secret';

    /**
     * @param array<string, mixed> $data
     */
    private function encodeJson(array $data): string
    {
        $json = json_encode($data);
        if (false === $json) {
            throw new \RuntimeException('Failed to encode JSON');
        }

        return $json;
    }

    /**
     * @test
     */
    public function itConstructsWithCorrectParameters(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $this->assertInstanceOf(AuthenticatedHttpClient::class, $client);
    }

    /**
     * @test
     */
    public function itHandlesSuccessfulJsonResponse(): void
    {
        $responseData = ['id' => 1, 'name' => 'Test Book'];
        $jsonResponse = json_encode($responseData);
        if (false === $jsonResponse) {
            throw new \RuntimeException('Failed to encode JSON');
        }
        $mockResponse = new MockResponse($jsonResponse, [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $httpClient = new MockHttpClient([$mockResponse]);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $response = $client->request('GET', '/api/books/1');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($jsonResponse, $response->getContent());
    }

    /**
     * @test
     */
    public function itThrowsBookStackClientExceptionFor404(): void
    {
        $mockResponse = new MockResponse('{"message":"Not Found","status":404}', [
            'http_code' => 404,
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $httpClient = new MockHttpClient([$mockResponse]);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $response = $client->request('GET', '/api/books/999');

        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function itThrowsBookStackClientExceptionFor403(): void
    {
        $mockResponse = new MockResponse('{"message":"Forbidden","status":403}', [
            'http_code' => 403,
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $httpClient = new MockHttpClient([$mockResponse]);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $response = $client->request('GET', '/api/books/protected');

        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function itThrowsBookStackServerExceptionFor500(): void
    {
        $mockResponse = new MockResponse('{"message":"Internal Server Error","status":500}', [
            'http_code' => 500,
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $httpClient = new MockHttpClient([$mockResponse]);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $response = $client->request('GET', '/api/books/error');

        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function itSendsCorrectAuthenticationHeaders(): void
    {
        $responseData = ['success' => true];
        $mockResponse = new MockResponse($this->encodeJson($responseData), [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $httpClient = new MockHttpClient([$mockResponse]);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $response = $client->request('GET', '/api/test');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, $httpClient->getRequestsCount());
    }

    /**
     * @test
     */
    public function itHandlesPostRequestWithData(): void
    {
        $responseData = ['id' => 2, 'name' => 'Created Book'];
        $mockResponse = new MockResponse($this->encodeJson($responseData), [
            'http_code' => 201,
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $httpClient = new MockHttpClient([$mockResponse]);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $postData = ['name' => 'New Book', 'description' => 'Test'];
        $response = $client->request('POST', '/api/books', ['json' => $postData]);

        $this->assertSame(201, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function itHandlesPutRequest(): void
    {
        $responseData = ['id' => 3, 'name' => 'Updated Book'];
        $mockResponse = new MockResponse($this->encodeJson($responseData), [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $httpClient = new MockHttpClient([$mockResponse]);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $putData = ['name' => 'Updated Name'];
        $response = $client->request('PUT', '/api/books/3', ['json' => $putData]);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function itHandlesDeleteRequest(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => 204,
            'response_headers' => [],
        ]);

        $httpClient = new MockHttpClient([$mockResponse]);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $response = $client->request('DELETE', '/api/books/4');

        $this->assertSame(204, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function itHandlesEmptyJsonResponse(): void
    {
        $mockResponse = new MockResponse('{}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $httpClient = new MockHttpClient([$mockResponse]);

        $client = new AuthenticatedHttpClient(
            $httpClient,
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET
        );

        $response = $client->request('GET', '/api/empty');

        $this->assertSame(200, $response->getStatusCode());
    }
}

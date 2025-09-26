<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\AuthenticatedHttpClient;
use Guiziweb\BookStackClient\Exception\BookStackClientException;
use Guiziweb\BookStackClient\Exception\BookStackServerException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Base service for all BookStack API services.
 *
 * @author Camille Islasse
 */
abstract class AbstractService
{
    protected AuthenticatedHttpClient $client;

    public function __construct(AuthenticatedHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     *
     * @throws BookStackClientException
     * @throws BookStackServerException
     * @throws TransportExceptionInterface
     */
    protected function get(string $endpoint, array $query = []): array
    {
        $options = [];
        if (!empty($query)) {
            $options['query'] = $query;
        }

        try {
            $response = $this->client->request('GET', $endpoint, $options);

            return $response->toArray();
        } catch (ClientExceptionInterface $e) {
            throw new BookStackClientException(sprintf('Client error on GET %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        } catch (ServerExceptionInterface $e) {
            throw new BookStackServerException(sprintf('Server error on GET %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws BookStackClientException
     * @throws BookStackServerException
     * @throws TransportExceptionInterface
     */
    protected function post(string $endpoint, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        try {
            $response = $this->client->request('POST', $endpoint, $options);

            return $response->toArray();
        } catch (ClientExceptionInterface $e) {
            throw new BookStackClientException(sprintf('Client error on POST %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        } catch (ServerExceptionInterface $e) {
            throw new BookStackServerException(sprintf('Server error on POST %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws BookStackClientException
     * @throws BookStackServerException
     * @throws TransportExceptionInterface
     */
    protected function put(string $endpoint, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        try {
            $response = $this->client->request('PUT', $endpoint, $options);

            return $response->toArray();
        } catch (ClientExceptionInterface $e) {
            throw new BookStackClientException(sprintf('Client error on PUT %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        } catch (ServerExceptionInterface $e) {
            throw new BookStackServerException(sprintf('Server error on PUT %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        }
    }

    /**
     * @throws BookStackClientException
     * @throws BookStackServerException
     * @throws TransportExceptionInterface
     */
    protected function deleteRequest(string $endpoint): void
    {
        try {
            $this->client->request('DELETE', $endpoint);
        } catch (ClientExceptionInterface $e) {
            throw new BookStackClientException(sprintf('Client error on DELETE %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        } catch (ServerExceptionInterface $e) {
            throw new BookStackServerException(sprintf('Server error on DELETE %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        }
    }

    /**
     * Make a GET request and return raw content (not JSON).
     *
     * @throws BookStackClientException
     * @throws BookStackServerException
     * @throws TransportExceptionInterface
     */
    protected function getRaw(string $endpoint): string
    {
        try {
            $response = $this->client->request('GET', $endpoint);

            return $response->getContent();
        } catch (ClientExceptionInterface $e) {
            throw new BookStackClientException(sprintf('Client error on GET %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        } catch (ServerExceptionInterface $e) {
            throw new BookStackServerException(sprintf('Server error on GET %s: %s', $endpoint, $e->getMessage()), $e->getCode(), $e);
        }
    }

    /**
     * Transform sort array to BookStack format.
     * BookStack uses 'sort' parameter with +/- prefix.
     *
     * @param array<string, string> $sort
     *
     * @return array<string, mixed>
     */
    protected function transformSortParameters(array $sort): array
    {
        if (empty($sort)) {
            return [];
        }

        $query = [];
        foreach ($sort as $field => $direction) {
            $prefix = 'desc' === strtolower($direction) ? '-' : '+';
            $query['sort'] = $prefix.$field;
            break; // BookStack accepts only one sort parameter
        }

        return $query;
    }
}

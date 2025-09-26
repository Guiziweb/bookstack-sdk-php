<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\SearchResult;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for searching BookStack content.
 *
 * @author Camille Islasse
 */
class SearchService extends AbstractService
{
    /**
     * Search content across BookStack.
     * Note: The general search endpoint does not support type filtering.
     * Use specific search methods (searchBooks, searchPages, etc.) for filtered searches.
     *
     * @return array{data: array<int, SearchResult>, total: int}
     */
    public function search(string $query, int $count = 20, int $page = 1): array
    {
        ParameterValidator::validateRequiredString($query, 'query');
        ParameterValidator::validateCount($count);
        ParameterValidator::validatePositiveInteger($page, 'page');

        $searchQuery = [
            'query' => $query,
            'count' => $count,
            'page' => $page,
        ];

        $response = $this->get('/api/search', $searchQuery);

        return [
            'data' => array_map(
                fn (array $searchData) => SearchResult::fromArray($searchData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Search only in books.
     * This filters search results to only return books from the general search.
     *
     * @return array{data: array<int, SearchResult>, total: int}
     */
    public function searchBooks(string $query, int $count = 20, int $page = 1): array
    {
        $results = $this->search($query, $count * 2, $page); // Request more results to account for filtering

        if (!isset($results['data'])) {
            return $results;
        }

        $filteredData = array_filter($results['data'], fn (SearchResult $item) => 'book' === $item->type);
        $results['data'] = array_slice($filteredData, 0, $count);
        $results['total'] = count($filteredData);

        return $results;
    }

    /**
     * Search only in pages.
     * This filters search results to only return pages from the general search.
     *
     * @return array{data: array<int, SearchResult>, total: int}
     */
    public function searchPages(string $query, int $count = 20, int $page = 1): array
    {
        $results = $this->search($query, $count * 2, $page);

        if (!isset($results['data'])) {
            return $results;
        }

        $filteredData = array_filter($results['data'], fn (SearchResult $item) => 'page' === $item->type);
        $results['data'] = array_slice($filteredData, 0, $count);
        $results['total'] = count($filteredData);

        return $results;
    }

    /**
     * Search only in chapters.
     * This filters search results to only return chapters from the general search.
     *
     * @return array{data: array<int, SearchResult>, total: int}
     */
    public function searchChapters(string $query, int $count = 20, int $page = 1): array
    {
        $results = $this->search($query, $count * 2, $page);

        if (!isset($results['data'])) {
            return $results;
        }

        $filteredData = array_filter($results['data'], fn (SearchResult $item) => 'chapter' === $item->type);
        $results['data'] = array_slice($filteredData, 0, $count);
        $results['total'] = count($filteredData);

        return $results;
    }

    /**
     * Search only in shelves.
     * This filters search results to only return shelves from the general search.
     *
     * @return array{data: array<int, SearchResult>, total: int}
     */
    public function searchShelves(string $query, int $count = 20, int $page = 1): array
    {
        $results = $this->search($query, $count * 2, $page);

        if (!isset($results['data'])) {
            return $results;
        }

        $filteredData = array_filter($results['data'], fn (SearchResult $item) => 'bookshelf' === $item->type);
        $results['data'] = array_slice($filteredData, 0, $count);
        $results['total'] = count($filteredData);

        return $results;
    }
}

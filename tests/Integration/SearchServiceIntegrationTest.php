<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\SearchResult;

/**
 * Integration tests for SearchService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class SearchServiceIntegrationTest extends BaseIntegration
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function itCanPerformGeneralSearch(): void
    {
        // Basic search
        $results = $this->client->search()->search('test');
        $this->assertIsArray($results);
        $this->assertArrayHasKey('data', $results);
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $results['data']);

        // With count parameter
        $results = $this->client->search()->search('admin', 5);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(5, count($results['data']));
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $results['data']);

        // With count and page
        $results = $this->client->search()->search('book', 10, 1);
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $results['data']);

        // With different page
        $results = $this->client->search()->search('page', 5, 2);
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $results['data']);
    }

    /**
     * @test
     */
    public function itCanPerformSpecializedSearches(): void
    {
        // Search books only (client-side filtering)
        $bookResults = $this->client->search()->searchBooks('test');
        $this->assertIsArray($bookResults);
        $this->assertArrayHasKey('data', $bookResults);
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $bookResults['data']);

        // Verify all results are books (if any)
        if (!empty($bookResults['data'])) {
            foreach ($bookResults['data'] as $result) {
                $this->assertEquals('book', $result->type, 'All results should be books');
            }
        }

        // Search pages only
        $pageResults = $this->client->search()->searchPages('test');
        $this->assertIsArray($pageResults);
        $this->assertArrayHasKey('data', $pageResults);
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $pageResults['data']);

        // Verify all results are pages (if any)
        if (!empty($pageResults['data'])) {
            foreach ($pageResults['data'] as $result) {
                $this->assertEquals('page', $result->type, 'All results should be pages');
            }
        }

        // Search chapters only
        $chapterResults = $this->client->search()->searchChapters('test');
        $this->assertIsArray($chapterResults);
        $this->assertArrayHasKey('data', $chapterResults);
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $chapterResults['data']);

        // Search shelves only
        $shelfResults = $this->client->search()->searchShelves('test');
        $this->assertIsArray($shelfResults);
        $this->assertArrayHasKey('data', $shelfResults);
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $shelfResults['data']);
    }

    /**
     * @test
     */
    public function itCanSearchWithVariousTerms(): void
    {
        $searchTerms = ['admin', 'book', 'page', 'chapter', 'content', 'guide'];

        foreach ($searchTerms as $term) {
            $results = $this->client->search()->search($term, 5);
            $this->assertIsArray($results, "Search for '$term' should return array");
            $this->assertArrayHasKey('data', $results, "Search for '$term' should have 'data' key");
            $this->assertContainsOnlyInstancesOf(SearchResult::class, $results['data']);
        }
    }

    /**
     * @test
     */
    public function itHandlesEmptySearchResultsGracefully(): void
    {
        // Search for something unlikely to exist
        $results = $this->client->search()->search('veryunlikelysearchterm12345', 5);
        $this->assertIsArray($results);
        $this->assertArrayHasKey('data', $results);
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $results['data']);
        // Empty results are valid
    }

    /**
     * @test
     */
    public function itCanSearchSpecializedMethodsWithDifferentTerms(): void
    {
        $terms = ['admin', 'test', 'book', 'page'];

        foreach ($terms as $term) {
            // Test all specialized search methods
            $bookResults = $this->client->search()->searchBooks($term);
            $this->assertIsArray($bookResults, "searchBooks('$term') should return array");

            $pageResults = $this->client->search()->searchPages($term);
            $this->assertIsArray($pageResults, "searchPages('$term') should return array");

            $chapterResults = $this->client->search()->searchChapters($term);
            $this->assertIsArray($chapterResults, "searchChapters('$term') should return array");

            $shelfResults = $this->client->search()->searchShelves($term);
            $this->assertIsArray($shelfResults, "searchShelves('$term') should return array");
        }
    }
}

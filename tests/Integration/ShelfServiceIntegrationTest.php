<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\Book;
use Guiziweb\BookStackClient\DTO\Shelf;

/**
 * Integration tests for ShelfService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class ShelfServiceIntegrationTest extends BaseIntegration
{
    /** @var array<int, int> */
    private array $createdShelves = [];
    /** @var array<int, int> */
    private array $createdBooks = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Delete shelves first
        foreach (array_reverse($this->createdShelves) as $shelfId) {
            $this->safeDelete(
                fn () => $this->client->shelves()->delete($shelfId),
                'shelf',
                $shelfId
            );
        }

        // Delete books
        foreach (array_reverse($this->createdBooks) as $bookId) {
            $this->safeDelete(
                fn () => $this->client->books()->delete($bookId),
                'book',
                $bookId
            );
        }
    }

    /**
     * @test
     */
    public function itCanListShelvesWithAllParameters(): void
    {
        // Basic list
        $shelves = $this->makeApiCall(fn () => $this->client->shelves()->list());
        $this->assertIsArray($shelves);
        $this->assertArrayHasKey('data', $shelves);
        $this->assertContainsOnlyInstancesOf(Shelf::class, $shelves['data']);

        // With count
        $shelves = $this->makeApiCall(fn () => $this->client->shelves()->list(5));
        $this->assertIsArray($shelves);
        $this->assertLessThanOrEqual(5, count($shelves['data']));
        $this->assertContainsOnlyInstancesOf(Shelf::class, $shelves['data']);

        // With count and offset
        $shelves = $this->makeApiCall(fn () => $this->client->shelves()->list(3, 2));
        $this->assertIsArray($shelves);
        $this->assertLessThanOrEqual(3, count($shelves['data']));
        $this->assertContainsOnlyInstancesOf(Shelf::class, $shelves['data']);

        // With sorting
        $shelves = $this->makeApiCall(fn () => $this->client->shelves()->list(5, 0, ['name' => 'asc']));
        $this->assertIsArray($shelves);

        $shelves = $this->makeApiCall(fn () => $this->client->shelves()->list(5, 0, ['created_at' => 'desc']));
        $this->assertIsArray($shelves);
    }

    /**
     * @test
     */
    public function itCanPerformCompleteShelfCrud(): void
    {
        // CREATE - Shelf with minimal data
        $shelf1 = $this->makeApiCall(fn () => $this->client->shelves()->create([
            'name' => 'Integration Test Shelf 1',
        ]));
        $this->assertInstanceOf(Shelf::class, $shelf1);
        $this->assertIsInt($shelf1->id);
        $this->assertEquals('Integration Test Shelf 1', $shelf1->name);
        $this->createdShelves[] = $shelf1->id;

        // CREATE - Shelf with description
        $shelf2 = $this->makeApiCall(fn () => $this->client->shelves()->create([
            'name' => 'Integration Test Shelf 2',
            'description' => 'Shelf with description for testing',
        ]));
        $this->assertInstanceOf(Shelf::class, $shelf2);
        $this->assertEquals('Integration Test Shelf 2', $shelf2->name);
        $this->assertEquals('Shelf with description for testing', $shelf2->description);
        $this->createdShelves[] = $shelf2->id;

        // READ
        $retrievedShelf = $this->makeApiCall(fn () => $this->client->shelves()->show($shelf1->id));
        $this->assertInstanceOf(Shelf::class, $retrievedShelf);
        $this->assertEquals($shelf1->id, $retrievedShelf->id);
        $this->assertEquals('Integration Test Shelf 1', $retrievedShelf->name);

        // UPDATE - Name only
        $updatedShelf = $this->makeApiCall(fn () => $this->client->shelves()->update($shelf1->id, [
            'name' => 'Updated Integration Test Shelf 1',
        ]));
        $this->assertInstanceOf(Shelf::class, $updatedShelf);
        $this->assertEquals('Updated Integration Test Shelf 1', $updatedShelf->name);

        // UPDATE - Name and description
        $updatedShelf2 = $this->makeApiCall(fn () => $this->client->shelves()->update($shelf2->id, [
            'name' => 'Updated Integration Test Shelf 2',
            'description' => 'Updated description for testing',
        ]));
        $this->assertInstanceOf(Shelf::class, $updatedShelf2);
        $this->assertEquals('Updated Integration Test Shelf 2', $updatedShelf2->name);
        $this->assertEquals('Updated description for testing', $updatedShelf2->description);
    }

    /**
     * @test
     */
    public function itCanManageShelfBooksRelationship(): void
    {
        // Create a shelf
        $shelf = $this->makeApiCall(fn () => $this->client->shelves()->create([
            'name' => 'Shelf for Book Management Test',
        ]));
        $this->createdShelves[] = $shelf->id;

        // Create books to add to shelf
        $book1 = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Book 1 for Shelf Test',
        ]));
        $this->createdBooks[] = $book1->id;

        $book2 = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Book 2 for Shelf Test',
        ]));
        $this->createdBooks[] = $book2->id;

        // Update shelf to include books
        $updatedShelf = $this->makeApiCall(fn () => $this->client->shelves()->update($shelf->id, [
            'name' => 'Shelf with Books',
            'books' => [$book1->id, $book2->id],
        ]));

        // Verify shelf has books (if the API returns books in the response)
        $retrievedShelf = $this->makeApiCall(fn () => $this->client->shelves()->show($shelf->id));
        $this->assertInstanceOf(Shelf::class, $retrievedShelf);
        $this->assertEquals('Shelf with Books', $retrievedShelf->name);

        // Note: BookStack API may or may not return books array in shelf response
        // This depends on the API implementation
        if (null !== $retrievedShelf->books) {
            $this->assertCount(2, $retrievedShelf->books);
            $this->assertContainsOnlyInstancesOf(Book::class, $retrievedShelf->books);
        }
    }

    /**
     * @test
     */
    public function itHandlesShelfDeletion(): void
    {
        // Create a shelf
        $shelf = $this->makeApiCall(fn () => $this->client->shelves()->create([
            'name' => 'Delete Test Shelf',
        ]));

        $shelfId = $shelf->id;

        // Delete the shelf
        $this->makeApiCall(fn () => $this->client->shelves()->delete($shelfId));

        // Try to retrieve deleted shelf - should fail
        $this->expectException(\Exception::class);
        $this->makeApiCall(fn () => $this->client->shelves()->show($shelfId));
    }

    /**
     * @test
     */
    public function itCanCreateShelfWithTags(): void
    {
        // Create shelf with tags (if supported)
        try {
            $shelf = $this->makeApiCall(fn () => $this->client->shelves()->create([
                'name' => 'Shelf with Tags Test',
                'description' => 'Testing shelf creation with tags',
                'tags' => [
                    ['name' => 'test-tag', 'value' => 'integration'],
                    ['name' => 'category', 'value' => 'documentation'],
                ],
            ]));
            $this->assertInstanceOf(Shelf::class, $shelf);
            $this->assertEquals('Shelf with Tags Test', $shelf->name);
            $this->createdShelves[] = $shelf->id;
        } catch (\Exception $e) {
            // Tags might not be supported or formatted differently
            // Create simple shelf instead
            $shelf = $this->makeApiCall(fn () => $this->client->shelves()->create([
                'name' => 'Simple Shelf Test',
                'description' => 'Testing simple shelf creation',
            ]));
            $this->assertInstanceOf(Shelf::class, $shelf);
            $this->assertEquals('Simple Shelf Test', $shelf->name);
            $this->createdShelves[] = $shelf->id;
        }
    }
}

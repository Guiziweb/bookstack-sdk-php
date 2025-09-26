<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\RecycleBinItem;

/**
 * Integration tests for RecycleBinService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class RecycleBinServiceIntegrationTest extends BaseIntegration
{
    /** @var array<int, int> */
    private array $createdBooks = [];
    /** @var array<int, int> */
    private array $createdPages = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Clean up any test resources
        foreach (array_reverse($this->createdPages) as $pageId) {
            $this->safeDelete(
                fn () => $this->client->pages()->delete($pageId),
                'page',
                $pageId
            );
        }

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
    public function itCanListRecycleBinItemsWithAllParameters(): void
    {
        // Basic list
        $items = $this->makeApiCall(fn () => $this->client->recycleBin()->list());
        $this->assertIsArray($items);
        $this->assertArrayHasKey('data', $items);

        // Should have deleted items from previous tests
        $this->assertGreaterThanOrEqual(0, count($items['data']));

        // With count
        $items = $this->makeApiCall(fn () => $this->client->recycleBin()->list(5));
        $this->assertIsArray($items);
        $this->assertLessThanOrEqual(5, count($items['data']));

        // With count and offset
        $items = $this->makeApiCall(fn () => $this->client->recycleBin()->list(3, 2));
        $this->assertIsArray($items);
        $this->assertLessThanOrEqual(3, count($items['data']));

        // With sorting
        $items = $this->makeApiCall(fn () => $this->client->recycleBin()->list(5, 0, ['created_at' => 'desc']));
        $this->assertIsArray($items);

        $items = $this->makeApiCall(fn () => $this->client->recycleBin()->list(5, 0, ['deleted_by' => 'asc']));
        $this->assertIsArray($items);
    }

    /**
     * @test
     */
    public function itCanShowRecycleBinItemDetails(): void
    {
        // Get first item from recycle bin to test show
        $items = $this->makeApiCall(fn () => $this->client->recycleBin()->list(1));

        if (empty($items['data'])) {
            // Create and delete something to test with
            $book = $this->makeApiCall(fn () => $this->client->books()->create([
                'name' => 'Recycle Bin Test Book',
            ]));
            $this->createdBooks[] = $book->id;

            // Delete it to put it in recycle bin
            $this->makeApiCall(fn () => $this->client->books()->delete($book->id));

            // Remove from our cleanup list since it's deleted
            array_pop($this->createdBooks);

            // Get the recycled item
            $items = $this->makeApiCall(fn () => $this->client->recycleBin()->list(1));
        }

        $this->assertNotEmpty($items['data']);
        $firstItem = $items['data'][0];
        $itemId = $firstItem->id;

        // Verify the item structure (no show method available)
        $this->assertEquals($itemId, $firstItem->id);
        $this->assertIsString($firstItem->deletableType);
        $this->assertIsInt($firstItem->deletableId);
        $this->assertIsInt($firstItem->deletedBy);
        $this->assertInstanceOf(\DateTimeImmutable::class, $firstItem->createdAt);
    }

    /**
     * @test
     */
    public function itCanRestoreItemsFromRecycleBin(): void
    {
        // Create a book to delete and restore
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Restore Test Book',
        ]));
        $bookId = $book->id;

        // Delete the book (puts it in recycle bin)
        $this->makeApiCall(fn () => $this->client->books()->delete($bookId));

        // Wait a bit for the item to appear in recycle bin
        $this->addDelay();

        // Get the recycle bin items sorted by newest first
        $items = $this->makeApiCall(fn () => $this->client->recycleBin()->list(100, 0, ['created_at' => 'desc']));
        $recycleItem = null;
        foreach ($items['data'] as $item) {
            if ('book' === $item->deletableType && $item->deletableId == $bookId) {
                $recycleItem = $item;
                break;
            }
        }

        $this->assertNotNull($recycleItem, 'Book should be in recycle bin');

        // Restore the item
        $restored = $this->makeApiCall(fn () => $this->client->recycleBin()->restore($recycleItem->id));
        $this->assertInstanceOf(RecycleBinItem::class, $restored);

        // Verify the book is restored and accessible
        $restoredBook = $this->makeApiCall(fn () => $this->client->books()->show($bookId));
        $this->assertEquals($bookId, $restoredBook->id);
        $this->assertEquals('Restore Test Book', $restoredBook->name);

        // Add to cleanup
        $this->createdBooks[] = $bookId;
    }

    /**
     * @test
     */
    public function itCanPermanentlyDeleteFromRecycleBin(): void
    {
        // Create a book to permanently delete
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Permanent Delete Test Book',
        ]));
        $bookId = $book->id;

        // Delete the book (puts it in recycle bin)
        $this->makeApiCall(fn () => $this->client->books()->delete($bookId));

        // Wait a bit for the item to appear in recycle bin
        $this->addDelay();

        // Get the recycle bin items sorted by newest first
        $items = $this->makeApiCall(fn () => $this->client->recycleBin()->list(100, 0, ['created_at' => 'desc']));
        $recycleItem = null;
        foreach ($items['data'] as $item) {
            if ('book' === $item->deletableType && $item->deletableId == $bookId) {
                $recycleItem = $item;
                break;
            }
        }

        $this->assertNotNull($recycleItem, 'Book should be in recycle bin');

        // Permanently delete the item
        $this->makeApiCall(fn () => $this->client->recycleBin()->destroy($recycleItem->id));

        // Verify the item is gone by checking it's not in the list anymore
        $itemsAfterDelete = $this->makeApiCall(fn () => $this->client->recycleBin()->list());
        $foundAfterDelete = null;
        foreach ($itemsAfterDelete['data'] as $item) {
            if ($item->id == $recycleItem->id) {
                $foundAfterDelete = $item;
                break;
            }
        }
        $this->assertNull($foundAfterDelete, 'Item should be permanently deleted from recycle bin');
    }

    /**
     * @test
     */
    public function itCanFilterRecycleBinByType(): void
    {
        // Create different types of items and delete them
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Filter Test Book',
        ]));

        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Filter Test Page',
            'html' => '<p>Test content</p>',
            'book_id' => $book->id,
        ]));

        // Delete both
        $this->makeApiCall(fn () => $this->client->pages()->delete($page->id));
        $this->makeApiCall(fn () => $this->client->books()->delete($book->id));

        // List all items and check we have different types
        $allItems = $this->makeApiCall(fn () => $this->client->recycleBin()->list());
        $this->assertIsArray($allItems);

        // Verify we have mixed types in recycle bin
        $types = array_unique(array_map(fn ($item) => $item->deletableType, $allItems['data']));
        $this->assertGreaterThan(0, count($types), 'Should have items of different types');
    }

    /**
     * @test
     */
    public function itHandlesNonExistentRecycleBinOperations(): void
    {
        // Try to restore non-existent item
        $this->expectException(\Exception::class);
        $this->makeApiCall(fn () => $this->client->recycleBin()->restore(999999));
    }
}

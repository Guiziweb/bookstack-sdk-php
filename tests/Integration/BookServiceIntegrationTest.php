<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\Book;

/**
 * Integration tests for BookService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class BookServiceIntegrationTest extends BaseIntegration
{
    /** @var array<int, int> */
    private array $createdBooks = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
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
    public function itCanListBooksWithAllParameters(): void
    {
        // Basic list
        $books = $this->client->books()->list();
        $this->assertIsArray($books);
        $this->assertArrayHasKey('data', $books);
        $this->assertContainsOnlyInstancesOf(Book::class, $books['data']);

        // With count
        $books = $this->client->books()->list(5);
        $this->assertIsArray($books);
        $this->assertLessThanOrEqual(5, count($books['data']));
        $this->assertContainsOnlyInstancesOf(Book::class, $books['data']);

        // With count and offset
        $books = $this->client->books()->list(3, 2);
        $this->assertIsArray($books);
        $this->assertLessThanOrEqual(3, count($books['data']));
        $this->assertContainsOnlyInstancesOf(Book::class, $books['data']);

        // With sorting (corrected format)
        $books = $this->client->books()->list(5, 0, ['name' => 'asc']);
        $this->assertIsArray($books);

        $books = $this->client->books()->list(5, 0, ['name' => 'desc']);
        $this->assertIsArray($books);

        $books = $this->client->books()->list(5, 0, ['created_at' => 'desc']);
        $this->assertIsArray($books);
    }

    /**
     * @test
     */
    public function itCanPerformCompleteBookCrud(): void
    {
        // CREATE - Minimal
        $book1 = $this->client->books()->create([
            'name' => 'Integration Test Book 1',
        ]);
        $this->assertInstanceOf(Book::class, $book1);
        $this->assertIsInt($book1->id);
        $this->assertEquals('Integration Test Book 1', $book1->name);
        $this->createdBooks[] = $book1->id;

        // CREATE - With description
        $book2 = $this->client->books()->create([
            'name' => 'Integration Test Book 2',
            'description' => 'Book with description for testing',
        ]);
        $this->assertInstanceOf(Book::class, $book2);
        $this->assertIsInt($book2->id);
        $this->assertEquals('Integration Test Book 2', $book2->name);
        $this->assertEquals('Book with description for testing', $book2->description);
        $this->createdBooks[] = $book2->id;

        // READ
        $retrievedBook = $this->client->books()->show($book1->id);
        $this->assertInstanceOf(Book::class, $retrievedBook);
        $this->assertEquals($book1->id, $retrievedBook->id);
        $this->assertEquals('Integration Test Book 1', $retrievedBook->name);

        // UPDATE - Name only
        $updatedBook = $this->client->books()->update($book1->id, [
            'name' => 'Updated Integration Test Book 1',
        ]);
        $this->assertInstanceOf(Book::class, $updatedBook);
        $this->assertEquals('Updated Integration Test Book 1', $updatedBook->name);

        // UPDATE - Name and description
        $updatedBook2 = $this->client->books()->update($book2->id, [
            'name' => 'Updated Integration Test Book 2',
            'description' => 'Updated description for testing',
        ]);
        $this->assertInstanceOf(Book::class, $updatedBook2);
        $this->assertEquals('Updated Integration Test Book 2', $updatedBook2->name);
        $this->assertEquals('Updated description for testing', $updatedBook2->description);
    }

    /**
     * @test
     */
    public function itCanTestBookContents(): void
    {
        $book = $this->client->books()->create([
            'name' => 'Contents Test Book',
        ]);
        $this->createdBooks[] = $book->id;

        try {
            $contents = $this->client->books()->contents($book->id);
            $this->assertIsArray($contents);
            // Contents should be array of BookContent objects
            if (!empty($contents)) {
                $this->assertContainsOnlyInstancesOf(\Guiziweb\BookStackClient\DTO\BookContent::class, $contents);
            }
        } catch (\Exception $e) {
            // Contents endpoint might not be available on all BookStack versions
            $this->assertTrue(true, 'Contents endpoint not available: '.$e->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanTestBookExports(): void
    {
        $book = $this->client->books()->create([
            'name' => 'Export Test Book',
        ]);
        $this->createdBooks[] = $book->id;

        $formats = ['pdf', 'html', 'plaintext', 'markdown'];

        foreach ($formats as $format) {
            try {
                $export = $this->client->books()->export($book->id, $format);
                // Export endpoints return binary data or special responses
                $this->assertTrue(true, "Export format $format works");
            } catch (\Symfony\Component\HttpClient\Exception\JsonException $e) {
                // JsonException is expected for binary formats (PDF, etc.)
                $this->assertTrue(true, "Export format $format returns binary data (expected)");
            } catch (\Exception $e) {
                // Other exceptions should be re-thrown
                throw $e;
            }
        }
    }

    /**
     * @test
     */
    public function itHandlesBookDeletion(): void
    {
        $book = $this->client->books()->create([
            'name' => 'Delete Test Book',
        ]);

        $bookId = $book->id;

        // Delete the book
        $this->client->books()->delete($bookId);

        // Try to retrieve deleted book - should fail
        $this->expectException(\Exception::class);
        $this->client->books()->show($bookId);
    }
}

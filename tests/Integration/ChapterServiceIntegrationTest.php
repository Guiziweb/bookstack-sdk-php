<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\Book;
use Guiziweb\BookStackClient\DTO\Chapter;

/**
 * Integration tests for ChapterService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class ChapterServiceIntegrationTest extends BaseIntegration
{
    /** @var array<int, int> */
    private array $createdBooks = [];
    /** @var array<int, int> */
    private array $createdChapters = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Delete chapters first
        foreach (array_reverse($this->createdChapters) as $chapterId) {
            $this->safeDelete(
                fn () => $this->client->chapters()->delete($chapterId),
                'chapter',
                $chapterId
            );
        }

        // Delete books last
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
    public function itCanListChaptersWithAllParameters(): void
    {
        // Basic list
        $chapters = $this->makeApiCall(fn () => $this->client->chapters()->list());
        $this->assertIsArray($chapters);
        $this->assertArrayHasKey('data', $chapters);
        $this->assertContainsOnlyInstancesOf(Chapter::class, $chapters['data']);

        // With count
        $chapters = $this->makeApiCall(fn () => $this->client->chapters()->list(5));
        $this->assertIsArray($chapters);
        $this->assertLessThanOrEqual(5, count($chapters['data']));
        $this->assertContainsOnlyInstancesOf(Chapter::class, $chapters['data']);

        // With count and offset
        $chapters = $this->makeApiCall(fn () => $this->client->chapters()->list(3, 2));
        $this->assertIsArray($chapters);
        $this->assertLessThanOrEqual(3, count($chapters['data']));
        $this->assertContainsOnlyInstancesOf(Chapter::class, $chapters['data']);

        // With sorting
        $chapters = $this->makeApiCall(fn () => $this->client->chapters()->list(5, 0, ['name' => 'asc']));
        $this->assertIsArray($chapters);

        $chapters = $this->makeApiCall(fn () => $this->client->chapters()->list(5, 0, ['created_at' => 'desc']));
        $this->assertIsArray($chapters);
    }

    /**
     * @test
     */
    public function itCanPerformCompleteChapterCrud(): void
    {
        // Create a book first (chapters require a book)
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Integration Test Book for Chapters',
        ]));
        $this->createdBooks[] = $book->id;

        // CREATE - Chapter with minimal data
        $chapter1 = $this->makeApiCall(fn () => $this->client->chapters()->create([
            'name' => 'Integration Test Chapter 1',
            'book_id' => $book->id,
        ]));
        $this->assertInstanceOf(Chapter::class, $chapter1);
        $this->assertIsInt($chapter1->id);
        $this->assertEquals('Integration Test Chapter 1', $chapter1->name);
        $this->assertEquals($book->id, $chapter1->bookId);
        $this->createdChapters[] = $chapter1->id;

        // CREATE - Chapter with description
        $chapter2 = $this->makeApiCall(fn () => $this->client->chapters()->create([
            'name' => 'Integration Test Chapter 2',
            'description' => 'Chapter with description for testing',
            'book_id' => $book->id,
        ]));
        $this->assertInstanceOf(Chapter::class, $chapter2);
        $this->assertEquals('Integration Test Chapter 2', $chapter2->name);
        $this->assertEquals('Chapter with description for testing', $chapter2->description);
        $this->createdChapters[] = $chapter2->id;

        // READ
        $retrievedChapter = $this->makeApiCall(fn () => $this->client->chapters()->show($chapter1->id));
        $this->assertInstanceOf(Chapter::class, $retrievedChapter);
        $this->assertEquals($chapter1->id, $retrievedChapter->id);
        $this->assertEquals('Integration Test Chapter 1', $retrievedChapter->name);

        // UPDATE - Name only
        $updatedChapter = $this->makeApiCall(fn () => $this->client->chapters()->update($chapter1->id, [
            'name' => 'Updated Integration Test Chapter 1',
        ]));
        $this->assertInstanceOf(Chapter::class, $updatedChapter);
        $this->assertEquals('Updated Integration Test Chapter 1', $updatedChapter->name);

        // UPDATE - Name and description
        $updatedChapter2 = $this->makeApiCall(fn () => $this->client->chapters()->update($chapter2->id, [
            'name' => 'Updated Integration Test Chapter 2',
            'description' => 'Updated description for testing',
        ]));
        $this->assertInstanceOf(Chapter::class, $updatedChapter2);
        $this->assertEquals('Updated Integration Test Chapter 2', $updatedChapter2->name);
        $this->assertEquals('Updated description for testing', $updatedChapter2->description);
    }

    /**
     * @test
     */
    public function itCanTestChapterExports(): void
    {
        // Create a book and chapter
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Export Test Book for Chapter',
        ]));
        $this->createdBooks[] = $book->id;

        $chapter = $this->makeApiCall(fn () => $this->client->chapters()->create([
            'name' => 'Export Test Chapter',
            'description' => 'This chapter will be exported in various formats',
            'book_id' => $book->id,
        ]));
        $this->createdChapters[] = $chapter->id;

        $formats = ['pdf', 'html', 'plaintext', 'markdown'];

        foreach ($formats as $format) {
            try {
                $export = $this->makeApiCall(fn () => $this->client->chapters()->export($chapter->id, $format));
                $this->assertIsString($export, "Export format $format should return string content");
                $this->assertNotEmpty($export, "Export format $format should not be empty");
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * @test
     */
    public function itHandlesChapterDeletion(): void
    {
        // Create a book and chapter
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Delete Test Book for Chapter',
        ]));
        $this->createdBooks[] = $book->id;

        $chapter = $this->makeApiCall(fn () => $this->client->chapters()->create([
            'name' => 'Delete Test Chapter',
            'book_id' => $book->id,
        ]));

        $chapterId = $chapter->id;

        // Delete the chapter
        $this->makeApiCall(fn () => $this->client->chapters()->delete($chapterId));

        // Try to retrieve deleted chapter - should fail
        $this->expectException(\Exception::class);
        $this->makeApiCall(fn () => $this->client->chapters()->show($chapterId));
    }

    /**
     * @test
     */
    public function itRequiresBookIdForChapterCreation(): void
    {
        // Try to create chapter without book_id - should fail
        $this->expectException(\Exception::class);
        $this->makeApiCall(fn () => $this->client->chapters()->create([
            'name' => 'Chapter Without Book',
        ]));
    }
}

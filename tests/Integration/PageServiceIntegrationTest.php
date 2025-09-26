<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\Book;
use Guiziweb\BookStackClient\DTO\Chapter;
use Guiziweb\BookStackClient\DTO\Page;

/**
 * Integration tests for PageService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class PageServiceIntegrationTest extends BaseIntegration
{
    /** @var array<int, int> */
    private array $createdBooks = [];
    /** @var array<int, int> */
    private array $createdPages = [];
    /** @var array<int, int> */
    private array $createdChapters = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Delete pages first (they depend on books/chapters)
        foreach (array_reverse($this->createdPages) as $pageId) {
            $this->safeDelete(
                fn () => $this->client->pages()->delete($pageId),
                'page',
                $pageId
            );
        }

        // Delete chapters
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
    public function itCanListPagesWithAllParameters(): void
    {
        // Basic list
        $pages = $this->makeApiCall(fn () => $this->client->pages()->list());
        $this->assertIsArray($pages);
        $this->assertArrayHasKey('data', $pages);
        $this->assertContainsOnlyInstancesOf(Page::class, $pages['data']);

        // With count
        $pages = $this->makeApiCall(fn () => $this->client->pages()->list(5));
        $this->assertIsArray($pages);
        $this->assertLessThanOrEqual(5, count($pages['data']));
        $this->assertContainsOnlyInstancesOf(Page::class, $pages['data']);

        // With count and offset
        $pages = $this->makeApiCall(fn () => $this->client->pages()->list(3, 2));
        $this->assertIsArray($pages);
        $this->assertLessThanOrEqual(3, count($pages['data']));
        $this->assertContainsOnlyInstancesOf(Page::class, $pages['data']);

        // With sorting
        $pages = $this->makeApiCall(fn () => $this->client->pages()->list(5, 0, ['name' => 'asc']));
        $this->assertIsArray($pages);

        $pages = $this->makeApiCall(fn () => $this->client->pages()->list(5, 0, ['created_at' => 'desc']));
        $this->assertIsArray($pages);
    }

    /**
     * @test
     */
    public function itCanPerformCompletePageCrudInBook(): void
    {
        // Create a book first
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Integration Test Book for Pages',
        ]));
        $this->createdBooks[] = $book->id;

        // CREATE - Page in book
        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Integration Test Page',
            'html' => '<p>This is a test page content</p>',
            'book_id' => $book->id,
        ]));
        $this->assertInstanceOf(Page::class, $page);
        $this->assertIsInt($page->id);
        $this->assertEquals('Integration Test Page', $page->name);
        $this->assertEquals($book->id, $page->bookId);
        $this->createdPages[] = $page->id;

        // READ
        $retrievedPage = $this->makeApiCall(fn () => $this->client->pages()->show($page->id));
        $this->assertInstanceOf(Page::class, $retrievedPage);
        $this->assertEquals($page->id, $retrievedPage->id);
        $this->assertEquals('Integration Test Page', $retrievedPage->name);

        // UPDATE
        $updatedPage = $this->makeApiCall(fn () => $this->client->pages()->update($page->id, [
            'name' => 'Updated Integration Test Page',
            'html' => '<p>This is updated page content</p>',
        ]));
        $this->assertInstanceOf(Page::class, $updatedPage);
        $this->assertEquals('Updated Integration Test Page', $updatedPage->name);
    }

    /**
     * @test
     */
    public function itCanPerformCompletePageCrudInChapter(): void
    {
        // Create a book first
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Integration Test Book for Chapter Pages',
        ]));
        $this->createdBooks[] = $book->id;

        // Create a chapter
        $chapter = $this->makeApiCall(fn () => $this->client->chapters()->create([
            'name' => 'Integration Test Chapter',
            'book_id' => $book->id,
        ]));
        $this->createdChapters[] = $chapter->id;

        // CREATE - Page in chapter
        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Integration Test Chapter Page',
            'html' => '<p>This is a test page in chapter</p>',
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
        ]));
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Integration Test Chapter Page', $page->name);
        $this->assertEquals($book->id, $page->bookId);
        $this->assertEquals($chapter->id, $page->chapterId);
        $this->createdPages[] = $page->id;

        // READ and verify chapter association
        $retrievedPage = $this->makeApiCall(fn () => $this->client->pages()->show($page->id));
        $this->assertInstanceOf(Page::class, $retrievedPage);
        $this->assertEquals($chapter->id, $retrievedPage->chapterId);
    }

    /**
     * @test
     */
    public function itCanTestPageExports(): void
    {
        // Create a book and page
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Export Test Book for Page',
        ]));
        $this->createdBooks[] = $book->id;

        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Export Test Page',
            'html' => '<h1>Test Content</h1><p>This page will be exported</p>',
            'book_id' => $book->id,
        ]));
        $this->createdPages[] = $page->id;

        $formats = ['pdf', 'html', 'plaintext', 'markdown'];

        foreach ($formats as $format) {
            try {
                $export = $this->makeApiCall(fn () => $this->client->pages()->export($page->id, $format));
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
    public function itHandlesPageDeletion(): void
    {
        // Create a book and page
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Delete Test Book for Page',
        ]));
        $this->createdBooks[] = $book->id;

        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Delete Test Page',
            'html' => '<p>This page will be deleted</p>',
            'book_id' => $book->id,
        ]));

        $pageId = $page->id;

        // Delete the page
        $this->makeApiCall(fn () => $this->client->pages()->delete($pageId));

        // Try to retrieve deleted page - should fail
        $this->expectException(\Exception::class);
        $this->makeApiCall(fn () => $this->client->pages()->show($pageId));
    }

    /**
     * @test
     */
    public function itCanCreatePagesWithMarkdown(): void
    {
        // Create a book
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Markdown Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        // Create page with markdown
        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Markdown Test Page',
            'markdown' => '# Test Heading\n\nThis is **bold** text with [a link](https://example.com)',
            'book_id' => $book->id,
        ]));
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Markdown Test Page', $page->name);
        $this->createdPages[] = $page->id;

        // Verify the page was created
        $retrievedPage = $this->makeApiCall(fn () => $this->client->pages()->show($page->id));
        $this->assertInstanceOf(Page::class, $retrievedPage);
        $this->assertEquals('Markdown Test Page', $retrievedPage->name);
    }
}

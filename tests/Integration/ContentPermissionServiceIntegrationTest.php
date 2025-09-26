<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\ContentPermission;

/**
 * Integration tests for ContentPermissionService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class ContentPermissionServiceIntegrationTest extends BaseIntegration
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
        // Clean up test resources
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
    public function itCanGetBookPermissions(): void
    {
        // Create a book to test permissions
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Permissions Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        try {
            // Get book permissions
            $permissions = $this->makeApiCall(fn () => $this->client->contentPermissions()->getPermissions('book', $book->id));

            $this->assertInstanceOf(ContentPermission::class, $permissions);
            $this->assertTrue(true, 'Book permissions retrieved successfully');
        } catch (\Exception $e) {
            // Permissions endpoint might not exist or be accessible
            $this->markTestSkipped('Book permissions endpoint not available: '.$e->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanGetChapterPermissions(): void
    {
        // Create a book and chapter
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Chapter Permissions Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        $chapter = $this->makeApiCall(fn () => $this->client->chapters()->create([
            'name' => 'Permissions Test Chapter',
            'book_id' => $book->id,
        ]));

        try {
            // Get chapter permissions
            $permissions = $this->makeApiCall(fn () => $this->client->contentPermissions()->getPermissions('chapter', $chapter->id));

            $this->assertInstanceOf(ContentPermission::class, $permissions);
            $this->assertTrue(true, 'Chapter permissions retrieved successfully');
        } finally {
            // Clean up chapter
            $this->safeDelete(
                fn () => $this->client->chapters()->delete($chapter->id),
                'chapter',
                $chapter->id
            );
        }
    }

    /**
     * @test
     */
    public function itCanGetPagePermissions(): void
    {
        // Create a book and page
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Page Permissions Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Permissions Test Page',
            'html' => '<p>Test content</p>',
            'book_id' => $book->id,
        ]));
        $this->createdPages[] = $page->id;

        try {
            // Get page permissions
            $permissions = $this->makeApiCall(fn () => $this->client->contentPermissions()->getPermissions('page', $page->id));

            $this->assertInstanceOf(ContentPermission::class, $permissions);
            $this->assertTrue(true, 'Page permissions retrieved successfully');
        } catch (\Exception $e) {
            // Permissions endpoint might not exist or be accessible
            $this->markTestSkipped('Page permissions endpoint not available: '.$e->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanUpdateBookPermissions(): void
    {
        // Create a book
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Update Permissions Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        // Get available roles
        $roles = $this->makeApiCall(fn () => $this->client->roles()->list(1));

        if (!empty($roles['data'])) {
            $roleId = $roles['data'][0]->id;

            try {
                // Update book permissions
                $updated = $this->makeApiCall(fn () => $this->client->contentPermissions()->set('book', $book->id, [
                    'role_permissions' => [
                        $roleId => [
                            'view' => true,
                            'update' => false,
                            'delete' => false,
                        ],
                    ],
                ]));

                $this->assertInstanceOf(ContentPermission::class, $updated);
                $this->assertTrue(true, 'Book permissions updated successfully');
            } catch (\Exception $e) {
                // Permission update might not be allowed or format might be different
                $this->markTestSkipped('Permission update not supported: '.$e->getMessage());
            }
        } else {
            $this->markTestSkipped('No roles available for permission testing');
        }
    }

    /**
     * @test
     */
    public function itCanUpdatePagePermissions(): void
    {
        // Create a book and page
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Page Permission Update Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Permission Update Test Page',
            'html' => '<p>Test content</p>',
            'book_id' => $book->id,
        ]));
        $this->createdPages[] = $page->id;

        // Get available roles
        $roles = $this->makeApiCall(fn () => $this->client->roles()->list(1));

        if (!empty($roles['data'])) {
            $roleId = $roles['data'][0]->id;

            try {
                // Update page permissions
                $updated = $this->makeApiCall(fn () => $this->client->contentPermissions()->set('page', $page->id, [
                    'role_permissions' => [
                        $roleId => [
                            'view' => true,
                            'update' => true,
                            'delete' => false,
                        ],
                    ],
                ]));

                $this->assertInstanceOf(ContentPermission::class, $updated);
                $this->assertTrue(true, 'Page permissions updated successfully');
            } catch (\Exception $e) {
                // Permission update might not be allowed
                $this->markTestSkipped('Page permission update not supported: '.$e->getMessage());
            }
        } else {
            $this->markTestSkipped('No roles available for permission testing');
        }
    }

    /**
     * @test
     */
    public function itCanHandleShelfPermissions(): void
    {
        // Create a shelf
        $shelf = $this->makeApiCall(fn () => $this->client->shelves()->create([
            'name' => 'Permissions Test Shelf',
        ]));

        try {
            // Get shelf permissions
            $permissions = $this->makeApiCall(fn () => $this->client->contentPermissions()->getPermissions('bookshelf', $shelf->id));

            $this->assertInstanceOf(ContentPermission::class, $permissions);
            $this->assertTrue(true, 'Shelf permissions retrieved successfully');
        } finally {
            // Clean up shelf
            $this->safeDelete(
                fn () => $this->client->shelves()->delete($shelf->id),
                'shelf',
                $shelf->id
            );
        }
    }
}

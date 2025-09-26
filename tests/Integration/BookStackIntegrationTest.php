<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\BookStackClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for BookStack API.
 * These tests require a running BookStack instance.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class BookStackIntegrationTest extends TestCase
{
    private \Guiziweb\BookStackClient\BookStackClient $client;
    /** @var array<string, array<int, int>> */
    private array $createdResources = [];

    protected function setUp(): void
    {
        $baseUrl = $_ENV['BOOKSTACK_BASE_URL'] ?? null;
        $apiKey = $_ENV['BOOKSTACK_API_KEY'] ?? null;
        $apiSecret = $_ENV['BOOKSTACK_API_SECRET'] ?? null;

        if (!$baseUrl || !$apiKey || !$apiSecret) {
            $this->markTestSkipped('BookStack credentials not configured');
        }

        $this->client = BookStackClientFactory::create($baseUrl, $apiKey, $apiSecret);
    }

    protected function tearDown(): void
    {
        // Cleanup created resources in reverse order
        $this->cleanupCreatedResources();
    }

    /**
     * @test
     */
    public function itCanListAllServices(): void
    {
        // Test all service endpoints are accessible
        $services = [
            'books' => fn () => $this->client->books()->list(5),
            'pages' => fn () => $this->client->pages()->list(5),
            'chapters' => fn () => $this->client->chapters()->list(5),
            'shelves' => fn () => $this->client->shelves()->list(5),
            'users' => fn () => $this->client->users()->list(5),
            'roles' => fn () => $this->client->roles()->list(5),
            'search' => fn () => $this->client->search()->search('test', 5),
            'images' => fn () => $this->client->images()->list(5),
            'attachments' => fn () => $this->client->attachments()->list(5),
            'recycleBin' => fn () => $this->client->recycleBin()->list(5),
            'auditLogs' => fn () => $this->client->auditLogs()->list(5),
        ];

        $successCount = 0;
        foreach ($services as $serviceName => $callback) {
            try {
                $result = $callback();
                $this->assertIsArray($result);
                $this->assertArrayHasKey('data', $result);
                ++$successCount;
            } catch (\Exception $e) {
                // Log but don't fail - some endpoints might not be available
                echo "Service $serviceName failed: ".$e->getMessage()."\n";
            }
        }

        $this->assertGreaterThanOrEqual(8, $successCount, 'At least 8 services should work');
    }

    /**
     * @test
     */
    public function itCanPerformBookCrudOperations(): void
    {
        // Create
        $book = $this->client->books()->create([
            'name' => 'PHPUnit Test Book',
            'description' => 'Book created by PHPUnit integration test',
        ]);

        $this->assertIsObject($book);
        $this->assertIsInt($book->id);
        $this->assertEquals('PHPUnit Test Book', $book->name);

        $bookId = $book->id;
        $this->createdResources['books'][] = $bookId;

        // Read
        $retrievedBook = $this->client->books()->show($bookId);
        $this->assertEquals($bookId, $retrievedBook->id);
        $this->assertEquals('PHPUnit Test Book', $retrievedBook->name);

        // Update
        $updatedBook = $this->client->books()->update($bookId, [
            'name' => 'Updated PHPUnit Test Book',
        ]);
        $this->assertEquals('Updated PHPUnit Test Book', $updatedBook->name);

        // List with sorting (corrected format)
        $books = $this->client->books()->list(10, 0, ['name' => 'asc']);
        $this->assertIsArray($books);
        $this->assertArrayHasKey('data', $books);
    }

    /**
     * @test
     */
    public function itCanPerformSearchOperations(): void
    {
        // General search (corrected - no types parameter)
        $results = $this->client->search()->search('test', 10, 1);
        $this->assertIsArray($results);
        $this->assertArrayHasKey('data', $results);

        // Specialized searches with client-side filtering
        $bookResults = $this->client->search()->searchBooks('test');
        $this->assertIsArray($bookResults);

        $pageResults = $this->client->search()->searchPages('test');
        $this->assertIsArray($pageResults);

        $chapterResults = $this->client->search()->searchChapters('test');
        $this->assertIsArray($chapterResults);

        $shelfResults = $this->client->search()->searchShelves('test');
        $this->assertIsArray($shelfResults);
    }

    /**
     * @test
     */
    public function itCanHandleUserManagement(): void
    {
        // Create user with unique email
        $uniqueEmail = 'phpunit.test.'.uniqid().'@example.com';
        $user = $this->client->users()->create([
            'name' => 'PHPUnit Test User',
            'email' => $uniqueEmail,
        ]);

        $this->assertIsObject($user);
        $this->assertIsInt($user->id);
        $userId = $user->id;
        $this->createdResources['users'][] = $userId;

        // Read user
        $retrievedUser = $this->client->users()->show($userId);
        $this->assertEquals($userId, $retrievedUser->id);
        $this->assertEquals($uniqueEmail, $retrievedUser->email);

        // Update user
        $updatedUser = $this->client->users()->update($userId, [
            'name' => 'Updated PHPUnit Test User',
        ]);
        $this->assertEquals('Updated PHPUnit Test User', $updatedUser->name);
    }

    /**
     * @test
     */
    public function itHandlesSortParametersCorrectly(): void
    {
        // Test corrected sort format (BookStack uses +/- prefix)
        $books = $this->client->books()->list(5, 0, ['name' => 'asc']);
        $this->assertIsArray($books);

        $users = $this->client->users()->list(5, 0, ['email' => 'desc']);
        $this->assertIsArray($users);

        $chapters = $this->client->chapters()->list(5, 0, ['created_at' => 'desc']);
        $this->assertIsArray($chapters);
    }

    /**
     * @test
     */
    public function itCanManageShelves(): void
    {
        $shelf = $this->client->shelves()->create([
            'name' => 'PHPUnit Test Shelf',
            'description' => 'Shelf created by PHPUnit',
        ]);

        $this->assertIsObject($shelf);
        $this->assertIsInt($shelf->id);
        $shelfId = $shelf->id;
        $this->createdResources['shelves'][] = $shelfId;

        $retrievedShelf = $this->client->shelves()->show($shelfId);
        $this->assertEquals($shelfId, $retrievedShelf->id);
    }

    /**
     * @test
     */
    public function itCanAccessAuditLogs(): void
    {
        $logs = $this->client->auditLogs()->list(10, 0);
        $this->assertIsArray($logs);
        $this->assertArrayHasKey('data', $logs);

        // With sorting
        $sortedLogs = $this->client->auditLogs()->list(5, 0, ['created_at' => 'desc']);
        $this->assertIsArray($sortedLogs);
    }

    /**
     * Clean up created resources in proper order (reverse dependency).
     */
    private function cleanupCreatedResources(): void
    {
        // Delete users
        if (isset($this->createdResources['users'])) {
            foreach (array_reverse($this->createdResources['users']) as $userId) {
                try {
                    $this->client->users()->delete($userId);
                } catch (\Exception $e) {
                    echo "Failed to delete user $userId: ".$e->getMessage()."\n";
                }
            }
        }

        // Delete shelves
        if (isset($this->createdResources['shelves'])) {
            foreach (array_reverse($this->createdResources['shelves']) as $shelfId) {
                try {
                    $this->client->shelves()->delete($shelfId);
                } catch (\Exception $e) {
                    echo "Failed to delete shelf $shelfId: ".$e->getMessage()."\n";
                }
            }
        }

        // Delete books
        if (isset($this->createdResources['books'])) {
            foreach (array_reverse($this->createdResources['books']) as $bookId) {
                try {
                    $this->client->books()->delete($bookId);
                } catch (\Exception $e) {
                    echo "Failed to delete book $bookId: ".$e->getMessage()."\n";
                }
            }
        }
    }
}

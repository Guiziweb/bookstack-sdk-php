<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

/**
 * Integration tests for AuditLogService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class AuditLogServiceIntegrationTest extends BaseIntegration
{
    /** @var array<int, int> */
    private array $createdBooks = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Clean up test resources
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
    public function itCanListAuditLogsWithAllParameters(): void
    {
        // Basic list
        $logs = $this->makeApiCall(fn () => $this->client->auditLogs()->list());
        $this->assertIsArray($logs);
        $this->assertArrayHasKey('data', $logs);

        // Should have audit logs from all previous operations
        $this->assertGreaterThan(0, count($logs['data']));

        // With count
        $logs = $this->makeApiCall(fn () => $this->client->auditLogs()->list(5));
        $this->assertIsArray($logs);
        $this->assertLessThanOrEqual(5, count($logs['data']));

        // With count and offset
        $logs = $this->makeApiCall(fn () => $this->client->auditLogs()->list(3, 2));
        $this->assertIsArray($logs);
        $this->assertLessThanOrEqual(3, count($logs['data']));

        // With sorting
        $logs = $this->makeApiCall(fn () => $this->client->auditLogs()->list(5, 0, ['created_at' => 'desc']));
        $this->assertIsArray($logs);

        $logs = $this->makeApiCall(fn () => $this->client->auditLogs()->list(5, 0, ['type' => 'asc']));
        $this->assertIsArray($logs);
    }

    /**
     * @test
     */
    public function itCanVerifyAuditLogStructure(): void
    {
        // Get first audit log to verify structure
        $logs = $this->makeApiCall(fn () => $this->client->auditLogs()->list(1));
        $this->assertArrayHasKey('data', $logs);
        $this->assertNotEmpty($logs['data']);

        $firstLog = $logs['data'][0];

        // Verify required fields
        $this->assertIsInt($firstLog->id);
        $this->assertIsString($firstLog->type);
        $this->assertIsString($firstLog->detail);
        $this->assertIsInt($firstLog->userId);
        $this->assertIsString($firstLog->createdAt);
        $this->assertIsString($firstLog->ip);

        // Verify user relationship
        $this->assertNotNull($firstLog->user);
        $this->assertIsInt($firstLog->user->id);
        $this->assertIsString($firstLog->user->name);
    }

    /**
     * @test
     */
    public function itCanTrackBookOperationsInAuditLog(): void
    {
        // Create a book to generate audit logs
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Audit Log Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        // Update the book
        $this->makeApiCall(fn () => $this->client->books()->update($book->id, [
            'name' => 'Updated Audit Log Test Book',
        ]));

        // Wait for logs to be recorded
        $this->addDelay();

        // Get recent audit logs
        $logs = $this->makeApiCall(fn () => $this->client->auditLogs()->list(50, 0, ['created_at' => 'desc']));

        // Find our book operations
        $createLog = null;
        $updateLog = null;

        foreach ($logs['data'] as $log) {
            if ('book_create' === $log->type
                && null !== $log->loggableId
                && $log->loggableId == $book->id) {
                $createLog = $log;
            }
            if ('book_update' === $log->type
                && null !== $log->loggableId
                && $log->loggableId == $book->id) {
                $updateLog = $log;
            }
        }

        $this->assertNotNull($createLog, 'Book creation should be in audit log');
        $this->assertNotNull($updateLog, 'Book update should be in audit log');

        // Verify log details
        $this->assertEquals('book_create', $createLog->type);
        $this->assertEquals('book', $createLog->loggableType);
        $this->assertStringContainsString('Audit Log Test Book', $createLog->detail);

        $this->assertEquals('book_update', $updateLog->type);
        $this->assertEquals('book', $updateLog->loggableType);
        $this->assertStringContainsString('Updated Audit Log Test Book', $updateLog->detail);
    }

    /**
     * @test
     */
    public function itCanFilterAuditLogsByType(): void
    {
        // Get audit logs and check for different types
        $logs = $this->makeApiCall(fn () => $this->client->auditLogs()->list(100));

        // Collect unique log types
        $types = array_unique(array_map(fn ($log) => $log->type, $logs['data']));

        // Should have various types of operations
        $this->assertGreaterThan(1, count($types), 'Should have different log types');

        // Common types we expect from our tests
        $expectedTypes = ['book_create', 'book_update', 'book_delete'];
        $foundTypes = array_intersect($expectedTypes, $types);

        $this->assertNotEmpty($foundTypes, 'Should have book operation logs');
    }

    /**
     * @test
     */
    public function itCanVerifyAuditLogPagination(): void
    {
        // Get total count
        $allLogs = $this->makeApiCall(fn () => $this->client->auditLogs()->list(1));
        $this->assertArrayHasKey('total', $allLogs);
        $total = $allLogs['total'];

        if ($total > 10) {
            // Test pagination
            $page1 = $this->makeApiCall(fn () => $this->client->auditLogs()->list(5, 0));
            $page2 = $this->makeApiCall(fn () => $this->client->auditLogs()->list(5, 5));

            // Verify different pages have different data
            $this->assertCount(5, $page1['data']);
            $this->assertLessThanOrEqual(5, count($page2['data']));

            // First item of page 2 should be different from all items in page 1
            if (!empty($page2['data'])) {
                $page2FirstId = $page2['data'][0]->id;
                $page1Ids = array_map(fn ($log) => $log->id, $page1['data']);
                $this->assertNotContains($page2FirstId, $page1Ids, 'Pages should contain different items');
            }
        } else {
            $this->markTestSkipped('Not enough audit log entries for pagination test');
        }
    }

    /**
     * @test
     */
    public function itCanTrackDeletionInAuditLog(): void
    {
        // Create and delete a book
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Delete Audit Test Book',
        ]));
        $bookId = $book->id;

        // Delete the book
        $this->makeApiCall(fn () => $this->client->books()->delete($bookId));

        // Wait for logs
        $this->addDelay();

        // Get recent audit logs
        $logs = $this->makeApiCall(fn () => $this->client->auditLogs()->list(50, 0, ['created_at' => 'desc']));

        // Find deletion log
        $deleteLog = null;
        foreach ($logs['data'] as $log) {
            if ('book_delete' === $log->type
                && null !== $log->loggableId
                && $log->loggableId == $bookId) {
                $deleteLog = $log;
                break;
            }
        }

        $this->assertNotNull($deleteLog, 'Book deletion should be in audit log');
        $this->assertEquals('book_delete', $deleteLog->type);
        $this->assertStringContainsString('Delete Audit Test Book', $deleteLog->detail);
    }
}

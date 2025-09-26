<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests;

use Guiziweb\BookStackClient\BookStackClient;
use Guiziweb\BookStackClient\Service\BookService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

/**
 * Test for BookStackClient.
 *
 * @author Camille Islasse
 */
class BookStackClientTest extends TestCase
{
    private BookStackClient $client;

    protected function setUp(): void
    {
        $httpClient = new MockHttpClient();
        $this->client = new BookStackClient($httpClient, 'https://test.com', 'test-api-key-123', 'test-api-secret-456');
    }

    public function testClientCreation(): void
    {
        $this->assertInstanceOf(BookStackClient::class, $this->client);
    }

    public function testBooksServiceAccess(): void
    {
        $this->assertInstanceOf(BookService::class, $this->client->books());
    }

    public function testAllServicesAreAccessible(): void
    {
        $this->assertInstanceOf(BookService::class, $this->client->books());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\PageService::class, $this->client->pages());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\ChapterService::class, $this->client->chapters());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\ShelfService::class, $this->client->shelves());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\SearchService::class, $this->client->search());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\AttachmentService::class, $this->client->attachments());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\ImageService::class, $this->client->images());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\UserService::class, $this->client->users());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\RoleService::class, $this->client->roles());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\RecycleBinService::class, $this->client->recycleBin());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\ContentPermissionService::class, $this->client->contentPermissions());
        $this->assertInstanceOf(\Guiziweb\BookStackClient\Service\AuditLogService::class, $this->client->auditLogs());
    }
}

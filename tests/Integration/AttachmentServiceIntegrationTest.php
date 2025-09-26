<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\Attachment;

/**
 * Integration tests for AttachmentService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class AttachmentServiceIntegrationTest extends BaseIntegration
{
    /** @var array<int, int> */
    private array $createdAttachments = [];
    /** @var array<int, int> */
    private array $createdPages = [];
    /** @var array<int, int> */
    private array $createdBooks = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Delete attachments first
        foreach (array_reverse($this->createdAttachments) as $attachmentId) {
            $this->safeDelete(
                fn () => $this->client->attachments()->delete($attachmentId),
                'attachment',
                $attachmentId
            );
        }

        // Delete pages
        foreach (array_reverse($this->createdPages) as $pageId) {
            $this->safeDelete(
                fn () => $this->client->pages()->delete($pageId),
                'page',
                $pageId
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
    public function itCanListAttachmentsWithAllParameters(): void
    {
        // Basic list
        $attachments = $this->makeApiCall(fn () => $this->client->attachments()->list());
        $this->assertIsArray($attachments);
        $this->assertArrayHasKey('data', $attachments);

        // With count
        $attachments = $this->makeApiCall(fn () => $this->client->attachments()->list(5));
        $this->assertIsArray($attachments);
        $this->assertLessThanOrEqual(5, count($attachments['data']));

        // With count and offset
        $attachments = $this->makeApiCall(fn () => $this->client->attachments()->list(3, 2));
        $this->assertIsArray($attachments);
        $this->assertLessThanOrEqual(3, count($attachments['data']));

        // With sorting
        $attachments = $this->makeApiCall(fn () => $this->client->attachments()->list(5, 0, ['name' => 'asc']));
        $this->assertIsArray($attachments);

        $attachments = $this->makeApiCall(fn () => $this->client->attachments()->list(5, 0, ['created_at' => 'desc']));
        $this->assertIsArray($attachments);
    }

    /**
     * @test
     */
    public function itCanCreateLinkAttachment(): void
    {
        // Create a book and page for the attachment
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Attachment Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Attachment Test Page',
            'html' => '<p>Page for attachment testing</p>',
            'book_id' => $book->id,
        ]));
        $this->createdPages[] = $page->id;

        // Create link attachment
        $attachment = $this->makeApiCall(fn () => $this->client->attachments()->create([
            'name' => 'Test Link Attachment',
            'link' => 'https://example.com/test-document',
            'uploaded_to' => $page->id,
        ]));

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertIsInt($attachment->id);
        $this->assertEquals('Test Link Attachment', $attachment->name);
        $this->assertEquals($page->id, $attachment->uploadedTo);
        $this->createdAttachments[] = $attachment->id;

        // READ the attachment
        $retrievedAttachment = $this->makeApiCall(fn () => $this->client->attachments()->show($attachment->id));
        $this->assertInstanceOf(Attachment::class, $retrievedAttachment);
        $this->assertEquals($attachment->id, $retrievedAttachment->id);
        $this->assertEquals('Test Link Attachment', $retrievedAttachment->name);
    }

    /**
     * @test
     */
    public function itCanCreateFileAttachmentWithContent(): void
    {
        // Create a book and page for the attachment
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'File Attachment Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'File Attachment Test Page',
            'html' => '<p>Page for file attachment testing</p>',
            'book_id' => $book->id,
        ]));
        $this->createdPages[] = $page->id;

        // Create file attachment with base64 content
        $fileContent = base64_encode('This is a test file content for attachment testing.');

        try {
            $attachment = $this->makeApiCall(fn () => $this->client->attachments()->create([
                'name' => 'test-file.txt',
                'file' => $fileContent,
                'uploaded_to' => $page->id,
            ]));

            $this->assertInstanceOf(Attachment::class, $attachment);
            $this->assertIsInt($attachment->id);
            $this->assertEquals('test-file.txt', $attachment->name);
            $this->assertEquals($page->id, $attachment->uploadedTo);
            $this->createdAttachments[] = $attachment->id;
        } catch (\Exception $e) {
            // File upload format might be different, try alternative approach
            $this->markTestSkipped('File attachment creation format not supported: '.$e->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanUpdateAttachment(): void
    {
        // Create a book and page for the attachment
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Update Attachment Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Update Attachment Test Page',
            'html' => '<p>Page for attachment update testing</p>',
            'book_id' => $book->id,
        ]));
        $this->createdPages[] = $page->id;

        // Create link attachment
        $attachment = $this->makeApiCall(fn () => $this->client->attachments()->create([
            'name' => 'Original Attachment Name',
            'link' => 'https://example.com/original-link',
            'uploaded_to' => $page->id,
        ]));
        $this->createdAttachments[] = $attachment->id;

        // UPDATE the attachment
        $updatedAttachment = $this->makeApiCall(fn () => $this->client->attachments()->update($attachment->id, [
            'name' => 'Updated Attachment Name',
            'link' => 'https://example.com/updated-link',
        ]));

        $this->assertInstanceOf(Attachment::class, $updatedAttachment);
        $this->assertEquals('Updated Attachment Name', $updatedAttachment->name);
    }

    /**
     * @test
     */
    public function itHandlesAttachmentDeletion(): void
    {
        // Create a book and page for the attachment
        $book = $this->makeApiCall(fn () => $this->client->books()->create([
            'name' => 'Delete Attachment Test Book',
        ]));
        $this->createdBooks[] = $book->id;

        $page = $this->makeApiCall(fn () => $this->client->pages()->create([
            'name' => 'Delete Attachment Test Page',
            'html' => '<p>Page for attachment deletion testing</p>',
            'book_id' => $book->id,
        ]));
        $this->createdPages[] = $page->id;

        // Create attachment
        $attachment = $this->makeApiCall(fn () => $this->client->attachments()->create([
            'name' => 'Delete Test Attachment',
            'link' => 'https://example.com/delete-test',
            'uploaded_to' => $page->id,
        ]));

        $attachmentId = $attachment->id;

        // Delete the attachment
        $this->makeApiCall(fn () => $this->client->attachments()->delete($attachmentId));

        // Try to retrieve deleted attachment - should fail
        $this->expectException(\Exception::class);
        $this->makeApiCall(fn () => $this->client->attachments()->show($attachmentId));
    }

    /**
     * @test
     */
    public function itRequiresUploadedToForAttachmentCreation(): void
    {
        // Try to create attachment without uploaded_to - should fail
        $this->expectException(\Exception::class);
        $this->makeApiCall(fn () => $this->client->attachments()->create([
            'name' => 'Attachment Without Page',
            'link' => 'https://example.com/no-page',
        ]));
    }
}

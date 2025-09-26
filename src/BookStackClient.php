<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient;

use Guiziweb\BookStackClient\Service\AttachmentService;
use Guiziweb\BookStackClient\Service\AuditLogService;
use Guiziweb\BookStackClient\Service\BookService;
use Guiziweb\BookStackClient\Service\ChapterService;
use Guiziweb\BookStackClient\Service\ContentPermissionService;
use Guiziweb\BookStackClient\Service\ImageService;
use Guiziweb\BookStackClient\Service\PageService;
use Guiziweb\BookStackClient\Service\RecycleBinService;
use Guiziweb\BookStackClient\Service\RoleService;
use Guiziweb\BookStackClient\Service\SearchService;
use Guiziweb\BookStackClient\Service\ShelfService;
use Guiziweb\BookStackClient\Service\UserService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Main BookStack API Client.
 *
 * @author Camille Islasse
 */
class BookStackClient
{
    private AttachmentService $attachmentService;
    private AuditLogService $auditLogService;
    private BookService $bookService;
    private ChapterService $chapterService;
    private ContentPermissionService $contentPermissionService;
    private ImageService $imageService;
    private PageService $pageService;
    private RecycleBinService $recycleBinService;
    private RoleService $roleService;
    private SearchService $searchService;
    private ShelfService $shelfService;
    private UserService $userService;

    public function __construct(
        HttpClientInterface $httpClient,
        string $baseUrl,
        string $apiKey,
        string $apiSecret,
    ) {
        $authenticatedClient = new AuthenticatedHttpClient($httpClient, $baseUrl, $apiKey, $apiSecret);

        $this->attachmentService = new AttachmentService($authenticatedClient);
        $this->auditLogService = new AuditLogService($authenticatedClient);
        $this->bookService = new BookService($authenticatedClient);
        $this->chapterService = new ChapterService($authenticatedClient);
        $this->contentPermissionService = new ContentPermissionService($authenticatedClient);
        $this->imageService = new ImageService($authenticatedClient);
        $this->pageService = new PageService($authenticatedClient);
        $this->recycleBinService = new RecycleBinService($authenticatedClient);
        $this->roleService = new RoleService($authenticatedClient);
        $this->searchService = new SearchService($authenticatedClient);
        $this->shelfService = new ShelfService($authenticatedClient);
        $this->userService = new UserService($authenticatedClient);
    }

    public function books(): BookService
    {
        return $this->bookService;
    }

    public function chapters(): ChapterService
    {
        return $this->chapterService;
    }

    public function pages(): PageService
    {
        return $this->pageService;
    }

    public function search(): SearchService
    {
        return $this->searchService;
    }

    public function shelves(): ShelfService
    {
        return $this->shelfService;
    }

    public function attachments(): AttachmentService
    {
        return $this->attachmentService;
    }

    public function images(): ImageService
    {
        return $this->imageService;
    }

    public function users(): UserService
    {
        return $this->userService;
    }

    public function roles(): RoleService
    {
        return $this->roleService;
    }

    public function recycleBin(): RecycleBinService
    {
        return $this->recycleBinService;
    }

    public function contentPermissions(): ContentPermissionService
    {
        return $this->contentPermissionService;
    }

    public function auditLogs(): AuditLogService
    {
        return $this->auditLogService;
    }
}

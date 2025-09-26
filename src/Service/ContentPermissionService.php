<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\ContentPermission;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack content permissions.
 *
 * @author Camille Islasse
 */
class ContentPermissionService extends AbstractService
{
    private const CONTENT_TYPES = ['book', 'chapter', 'page', 'bookshelf'];

    /**
     * Get permissions for content.
     */
    public function getPermissions(string $contentType, int $contentId): ContentPermission
    {
        $this->validateContentType($contentType);
        ParameterValidator::validatePositiveInteger($contentId, 'contentId');

        $response = $this->get("/api/content-permissions/{$contentType}/{$contentId}");

        return ContentPermission::fromArray($response);
    }

    /**
     * Set permissions for content.
     *
     * @param array<string, mixed> $permissions
     */
    public function set(string $contentType, int $contentId, array $permissions): ContentPermission
    {
        $this->validateContentType($contentType);
        ParameterValidator::validatePositiveInteger($contentId, 'contentId');

        $response = $this->put("/api/content-permissions/{$contentType}/{$contentId}", $permissions);

        return ContentPermission::fromArray($response);
    }

    private function validateContentType(string $contentType): void
    {
        if (!in_array($contentType, self::CONTENT_TYPES, true)) {
            throw new \Guiziweb\BookStackClient\Exception\ValidationException(sprintf('Invalid content type "%s". Allowed types: %s', $contentType, implode(', ', self::CONTENT_TYPES)));
        }
    }
}

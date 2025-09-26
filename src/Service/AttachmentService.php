<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\Attachment;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack attachments.
 *
 * @author Camille Islasse
 */
class AttachmentService extends AbstractService
{
    /**
     * List all attachments.
     *
     * @param array<string, string> $sort
     *
     * @return array{data: array<int, Attachment>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];

        $query = array_merge($query, $this->transformSortParameters($sort));

        $response = $this->get('/api/attachments', $query);

        return [
            'data' => array_map(
                fn (array $attachmentData) => Attachment::fromArray($attachmentData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Create a new attachment.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Attachment
    {
        ParameterValidator::validateRequiredFields($data, ['name', 'uploaded_to']);

        $response = $this->post('/api/attachments', $data);

        return Attachment::fromArray($response);
    }

    /**
     * Get a specific attachment by ID.
     */
    public function show(int $id): Attachment
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->get("/api/attachments/{$id}");

        return Attachment::fromArray($response);
    }

    /**
     * Update a specific attachment.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Attachment
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->put("/api/attachments/{$id}", $data);

        return Attachment::fromArray($response);
    }

    /**
     * Delete a specific attachment.
     */
    public function delete(int $id): void
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $this->deleteRequest("/api/attachments/{$id}");
    }
}

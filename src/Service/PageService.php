<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\Page;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack pages.
 *
 * @author Camille Islasse
 */
class PageService extends AbstractService
{
    /**
     * List all pages.
     *
     * @param array<string, string> $sort
     *
     * @return array{data: array<int, Page>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];
        $query = array_merge($query, $this->transformSortParameters($sort));

        $response = $this->get('/api/pages', $query);

        return [
            'data' => array_map(
                fn (array $pageData) => Page::fromArray($pageData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Create a new page.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Page
    {
        ParameterValidator::validateRequiredFields($data, ['name', 'book_id']);

        $response = $this->post('/api/pages', $data);

        return Page::fromArray($response);
    }

    /**
     * Get a specific page by ID.
     */
    public function show(int $id): Page
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->get("/api/pages/{$id}");

        return Page::fromArray($response);
    }

    /**
     * Update a specific page.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Page
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->put("/api/pages/{$id}", $data);

        return Page::fromArray($response);
    }

    /**
     * Delete a specific page.
     */
    public function delete(int $id): void
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $this->deleteRequest("/api/pages/{$id}");
    }

    /**
     * Export a page in the specified format.
     */
    public function export(int $id, string $format = 'pdf'): string
    {
        ParameterValidator::validatePositiveInteger($id, 'id');
        ParameterValidator::validateExportFormat($format);

        return $this->getRaw("/api/pages/{$id}/export/{$format}");
    }
}

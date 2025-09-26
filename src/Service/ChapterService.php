<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\Chapter;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack chapters.
 *
 * @author Camille Islasse
 */
class ChapterService extends AbstractService
{
    /**
     * List all chapters.
     *
     * @param array<string, string> $sort
     *
     * @return array{data: array<int, Chapter>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];
        $query = array_merge($query, $this->transformSortParameters($sort));

        $response = $this->get('/api/chapters', $query);

        return [
            'data' => array_map(
                fn (array $chapterData) => Chapter::fromArray($chapterData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Create a new chapter.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Chapter
    {
        ParameterValidator::validateRequiredFields($data, ['name', 'book_id']);

        $response = $this->post('/api/chapters', $data);

        return Chapter::fromArray($response);
    }

    /**
     * Get a specific chapter by ID.
     */
    public function show(int $id): Chapter
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->get("/api/chapters/{$id}");

        return Chapter::fromArray($response);
    }

    /**
     * Update a specific chapter.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Chapter
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->put("/api/chapters/{$id}", $data);

        return Chapter::fromArray($response);
    }

    /**
     * Delete a specific chapter.
     */
    public function delete(int $id): void
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $this->deleteRequest("/api/chapters/{$id}");
    }

    /**
     * Export a chapter in the specified format.
     */
    public function export(int $id, string $format = 'pdf'): string
    {
        ParameterValidator::validatePositiveInteger($id, 'id');
        ParameterValidator::validateExportFormat($format);

        return $this->getRaw("/api/chapters/{$id}/export/{$format}");
    }
}

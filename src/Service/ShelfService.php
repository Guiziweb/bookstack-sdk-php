<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\Shelf;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack shelves.
 *
 * @author Camille Islasse
 */
class ShelfService extends AbstractService
{
    /**
     * List all shelves.
     *
     * @param array<string, string> $sort
     *
     * @return array{data: array<int, Shelf>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];
        $query = array_merge($query, $this->transformSortParameters($sort));

        $response = $this->get('/api/shelves', $query);

        return [
            'data' => array_map(
                fn (array $shelfData) => Shelf::fromArray($shelfData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Create a new shelf.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Shelf
    {
        ParameterValidator::validateRequiredFields($data, ['name']);

        $response = $this->post('/api/shelves', $data);

        return Shelf::fromArray($response);
    }

    /**
     * Get a specific shelf by ID.
     */
    public function show(int $id): Shelf
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->get("/api/shelves/{$id}");

        return Shelf::fromArray($response);
    }

    /**
     * Update a specific shelf.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Shelf
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->put("/api/shelves/{$id}", $data);

        return Shelf::fromArray($response);
    }

    /**
     * Delete a specific shelf.
     */
    public function delete(int $id): void
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $this->deleteRequest("/api/shelves/{$id}");
    }
}

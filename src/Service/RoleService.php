<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\Role;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack roles.
 *
 * @author Camille Islasse
 */
class RoleService extends AbstractService
{
    /**
     * List all roles.
     *
     * @param array<string, string> $sort
     *
     * @return array{data: array<int, Role>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];

        $query = array_merge($query, $this->transformSortParameters($sort));

        $response = $this->get('/api/roles', $query);

        return [
            'data' => array_map(
                fn (array $roleData) => Role::fromArray($roleData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Create a new role.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Role
    {
        ParameterValidator::validateRequiredFields($data, ['display_name']);

        $response = $this->post('/api/roles', $data);

        return Role::fromArray($response);
    }

    /**
     * Get a specific role by ID.
     */
    public function show(int $id): Role
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->get("/api/roles/{$id}");

        return Role::fromArray($response);
    }

    /**
     * Update a specific role.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Role
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->put("/api/roles/{$id}", $data);

        return Role::fromArray($response);
    }

    /**
     * Delete a specific role.
     */
    public function delete(int $id): void
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $this->deleteRequest("/api/roles/{$id}");
    }
}

<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\User;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack users.
 *
 * @author Camille Islasse
 */
class UserService extends AbstractService
{
    /**
     * List all users.
     *
     * @param array<string, string> $sort
     *
     * @return array{data: array<int, User>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];
        $query = array_merge($query, $this->transformSortParameters($sort));

        $response = $this->get('/api/users', $query);

        return [
            'data' => array_map(
                fn (array $userData) => User::fromArray($userData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Create a new user.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): User
    {
        ParameterValidator::validateRequiredFields($data, ['name', 'email']);

        $response = $this->post('/api/users', $data);

        return User::fromArray($response);
    }

    /**
     * Get a specific user by ID.
     */
    public function show(int $id): User
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->get("/api/users/{$id}");

        return User::fromArray($response);
    }

    /**
     * Update a specific user.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): User
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->put("/api/users/{$id}", $data);

        return User::fromArray($response);
    }

    /**
     * Delete a specific user.
     */
    public function delete(int $id): void
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $this->deleteRequest("/api/users/{$id}");
    }
}

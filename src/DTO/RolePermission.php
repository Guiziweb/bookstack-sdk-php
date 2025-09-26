<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * RolePermission data transfer object.
 *
 * @author Camille Islasse
 */
readonly class RolePermission
{
    public function __construct(
        public int $roleId,
        public bool $view,
        public bool $create,
        public bool $update,
        public bool $delete,
        public ?PermissionRole $role = null,
    ) {
    }

    /**
     * Create RolePermission from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            roleId: (int) $data['role_id'],
            view: (bool) $data['view'],
            create: (bool) $data['create'],
            update: (bool) $data['update'],
            delete: (bool) $data['delete'],
            role: isset($data['role']) && is_array($data['role'])
                ? PermissionRole::fromArray($data['role'])
                : null
        );
    }

    /**
     * Convert to array for API requests.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'role_id' => $this->roleId,
            'view' => $this->view,
            'create' => $this->create,
            'update' => $this->update,
            'delete' => $this->delete,
            'role' => $this->role?->toArray(),
        ], static fn ($value): bool => null !== $value);
    }
}

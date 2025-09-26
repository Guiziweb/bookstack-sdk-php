<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * ContentPermission data transfer object.
 *
 * @author Camille Islasse
 */
readonly class ContentPermission
{
    /**
     * @param array<RolePermission>|null $rolePermissions
     */
    public function __construct(
        public ?PermissionOwner $owner = null,
        public ?array $rolePermissions = null,
        public ?FallbackPermissions $fallbackPermissions = null,
    ) {
    }

    /**
     * Create ContentPermission from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $rolePermissions = null;
        if (isset($data['role_permissions']) && is_array($data['role_permissions'])) {
            $rolePermissions = array_map(
                fn (array $rolePermData) => RolePermission::fromArray($rolePermData),
                $data['role_permissions']
            );
        }

        return new self(
            owner: isset($data['owner']) && is_array($data['owner'])
                ? PermissionOwner::fromArray($data['owner'])
                : null,
            rolePermissions: $rolePermissions,
            fallbackPermissions: isset($data['fallback_permissions']) && is_array($data['fallback_permissions'])
                ? FallbackPermissions::fromArray($data['fallback_permissions'])
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
            'owner' => $this->owner?->toArray(),
            'role_permissions' => null !== $this->rolePermissions
                ? array_map(fn (RolePermission $rolePerm) => $rolePerm->toArray(), $this->rolePermissions)
                : null,
            'fallback_permissions' => $this->fallbackPermissions?->toArray(),
        ], static fn ($value): bool => null !== $value);
    }
}

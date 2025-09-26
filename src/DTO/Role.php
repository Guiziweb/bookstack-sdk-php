<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Role data transfer object.
 *
 * @author Camille Islasse
 */
readonly class Role
{
    /**
     * @param array<string>|null   $permissions
     * @param array<RoleUser>|null $users
     */
    public function __construct(
        public int $id,
        public string $displayName,
        public ?string $description = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?string $systemName = null,
        public ?string $externalAuthId = null,
        public ?bool $mfaEnforced = null,
        public ?int $usersCount = null,
        public ?int $permissionsCount = null,
        public ?array $permissions = null,
        public ?array $users = null,
    ) {
    }

    /**
     * Create Role from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $users = null;
        if (isset($data['users']) && is_array($data['users'])) {
            $users = array_map(
                fn (array $user) => RoleUser::fromArray($user),
                $data['users']
            );
        }

        return new self(
            id: (int) $data['id'],
            displayName: (string) $data['display_name'],
            description: $data['description'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            systemName: $data['system_name'] ?? null,
            externalAuthId: $data['external_auth_id'] ?? null,
            mfaEnforced: isset($data['mfa_enforced']) ? (bool) $data['mfa_enforced'] : null,
            usersCount: isset($data['users_count']) ? (int) $data['users_count'] : null,
            permissionsCount: isset($data['permissions_count']) ? (int) $data['permissions_count'] : null,
            permissions: isset($data['permissions']) && is_array($data['permissions'])
                ? array_map('strval', $data['permissions'])
                : null,
            users: $users
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
            'id' => $this->id,
            'display_name' => $this->displayName,
            'description' => $this->description,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'system_name' => $this->systemName,
            'external_auth_id' => $this->externalAuthId,
            'mfa_enforced' => $this->mfaEnforced,
            'users_count' => $this->usersCount,
            'permissions_count' => $this->permissionsCount,
            'permissions' => $this->permissions,
            'users' => null !== $this->users
                ? array_map(fn (RoleUser $user) => $user->toArray(), $this->users)
                : null,
        ], static fn ($value): bool => null !== $value);
    }
}

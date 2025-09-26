<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * User data transfer object.
 *
 * @author Camille Islasse
 */
readonly class User
{
    /**
     * @param array<UserRole>|null $roles
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $slug,
        public ?string $externalAuthId = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?\DateTimeImmutable $lastActivityAt = null,
        public ?string $profileUrl = null,
        public ?string $editUrl = null,
        public ?string $avatarUrl = null,
        public ?array $roles = null,
    ) {
    }

    /**
     * Create User from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $roles = null;
        if (isset($data['roles']) && is_array($data['roles'])) {
            $roles = array_map(
                fn (array $role) => UserRole::fromArray($role),
                $data['roles']
            );
        }

        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            email: (string) $data['email'],
            slug: (string) $data['slug'],
            externalAuthId: $data['external_auth_id'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            lastActivityAt: isset($data['last_activity_at']) ? new \DateTimeImmutable($data['last_activity_at']) : null,
            profileUrl: $data['profile_url'] ?? null,
            editUrl: $data['edit_url'] ?? null,
            avatarUrl: $data['avatar_url'] ?? null,
            roles: $roles
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
            'name' => $this->name,
            'email' => $this->email,
            'slug' => $this->slug,
            'external_auth_id' => $this->externalAuthId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'last_activity_at' => $this->lastActivityAt?->format('Y-m-d H:i:s'),
            'profile_url' => $this->profileUrl,
            'edit_url' => $this->editUrl,
            'avatar_url' => $this->avatarUrl,
            'roles' => null !== $this->roles
                ? array_map(fn (UserRole $role) => $role->toArray(), $this->roles)
                : null,
        ], static fn ($value): bool => null !== $value);
    }
}

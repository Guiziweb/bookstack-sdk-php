<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * PermissionOwner data transfer object.
 *
 * @author Camille Islasse
 */
readonly class PermissionOwner
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
    ) {
    }

    /**
     * Create PermissionOwner from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            slug: (string) $data['slug']
        );
    }

    /**
     * Convert to array for API requests.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}

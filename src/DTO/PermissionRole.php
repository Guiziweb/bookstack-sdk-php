<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * PermissionRole data transfer object.
 *
 * @author Camille Islasse
 */
readonly class PermissionRole
{
    public function __construct(
        public int $id,
        public string $displayName,
    ) {
    }

    /**
     * Create PermissionRole from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            displayName: (string) $data['display_name']
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
            'display_name' => $this->displayName,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * RecycleBinItem data transfer object.
 *
 * @author Camille Islasse
 */
readonly class RecycleBinItem
{
    /**
     * @param array<string, mixed>|null $deletable
     */
    public function __construct(
        public int $id,
        public int $deletedBy,
        public string $deletableType,
        public int $deletableId,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?array $deletable = null,
    ) {
    }

    /**
     * Create RecycleBinItem from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            deletedBy: (int) ($data['deleted_by'] ?? 0),
            deletableType: (string) ($data['deletable_type'] ?? ''),
            deletableId: (int) ($data['deletable_id'] ?? 0),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            deletable: $data['deletable'] ?? null
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
            'deleted_by' => $this->deletedBy,
            'deletable_type' => $this->deletableType,
            'deletable_id' => $this->deletableId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'deletable' => $this->deletable,
        ], static fn ($value): bool => null !== $value);
    }
}

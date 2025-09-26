<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Attachment data transfer object.
 *
 * @author Camille Islasse
 */
readonly class Attachment
{
    public function __construct(
        public int $id,
        public string $name,
        public string $extension,
        public int $uploadedTo,
        public bool $external,
        public int $order,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
    ) {
    }

    /**
     * Create Attachment from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            extension: (string) $data['extension'],
            uploadedTo: (int) $data['uploaded_to'],
            external: (bool) $data['external'],
            order: (int) $data['order'],
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null,
            updatedBy: isset($data['updated_by']) ? (int) $data['updated_by'] : null
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
            'extension' => $this->extension,
            'uploaded_to' => $this->uploadedTo,
            'external' => $this->external,
            'order' => $this->order,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
        ], static fn ($value): bool => null !== $value);
    }
}

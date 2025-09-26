<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Image data transfer object.
 *
 * @author Camille Islasse
 */
readonly class Image
{
    /**
     * @param array<string, string>|null $thumbs
     * @param array<string, string>|null $content
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $url,
        public string $path,
        public string $type,
        public ?int $uploadedTo = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?ImageUser $createdBy = null,
        public ?ImageUser $updatedBy = null,
        public ?array $thumbs = null,
        public ?array $content = null,
    ) {
    }

    /**
     * Create Image from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            url: (string) $data['url'],
            path: (string) $data['path'],
            type: (string) $data['type'],
            uploadedTo: isset($data['uploaded_to']) ? (int) $data['uploaded_to'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            createdBy: isset($data['created_by']) ? ImageUser::fromArray($data['created_by']) : null,
            updatedBy: isset($data['updated_by']) ? ImageUser::fromArray($data['updated_by']) : null,
            thumbs: isset($data['thumbs']) && is_array($data['thumbs'])
                ? array_map('strval', $data['thumbs'])
                : null,
            content: isset($data['content']) && is_array($data['content'])
                ? array_map('strval', $data['content'])
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
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'path' => $this->path,
            'type' => $this->type,
            'uploaded_to' => $this->uploadedTo,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'created_by' => $this->createdBy?->toArray(),
            'updated_by' => $this->updatedBy?->toArray(),
            'thumbs' => $this->thumbs,
            'content' => $this->content,
        ], static fn ($value): bool => null !== $value);
    }
}

<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Cover image data transfer object.
 *
 * @author Camille Islasse
 */
readonly class Cover
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $url = null,
        public ?string $path = null,
        public ?string $type = null,
        public ?int $uploadedTo = null,
    ) {
    }

    /**
     * Create Cover from API response array.
     *
     * @param array<string, mixed>|null $data
     */
    public static function fromArray(?array $data): ?self
    {
        if (null === $data) {
            return null;
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'] ?? null,
            url: $data['url'] ?? null,
            path: $data['path'] ?? null,
            type: $data['type'] ?? null,
            uploadedTo: isset($data['uploaded_to']) ? (int) $data['uploaded_to'] : null
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
        ], static fn ($value): bool => null !== $value);
    }
}

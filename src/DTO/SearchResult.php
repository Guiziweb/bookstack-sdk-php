<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Search result data transfer object.
 *
 * @author Camille Islasse
 */
readonly class SearchResult
{
    /**
     * @param array<int, array{name: string, value: string}>|null $tags
     */
    public function __construct(
        public string $type,
        public int $id,
        public string $name,
        public string $slug,
        public ?string $url = null,
        public ?int $bookId = null,
        public ?int $chapterId = null,
        public ?string $preview = null,
        public ?array $tags = null,
    ) {
    }

    /**
     * Create SearchResult from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: (string) $data['type'],
            id: (int) $data['id'],
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            url: $data['url'] ?? null,
            bookId: isset($data['book_id']) ? (int) $data['book_id'] : null,
            chapterId: isset($data['chapter_id']) ? (int) $data['chapter_id'] : null,
            preview: $data['preview'] ?? null,
            tags: $data['tags'] ?? null
        );
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => $this->url,
            'book_id' => $this->bookId,
            'chapter_id' => $this->chapterId,
            'preview' => $this->preview,
            'tags' => $this->tags,
        ], static fn ($value): bool => null !== $value);
    }
}

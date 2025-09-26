<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Shelf data transfer object.
 *
 * @author Camille Islasse
 */
readonly class Shelf
{
    /**
     * @param array<Tag>|null  $tags
     * @param array<Book>|null $books
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $descriptionHtml = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
        public ?int $ownedBy = null,
        public ?array $tags = null,
        public ?Cover $cover = null,
        public ?array $books = null,
    ) {
    }

    /**
     * Create Shelf from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $tags = null;
        if (isset($data['tags']) && is_array($data['tags'])) {
            $tags = array_map(
                fn (array $tag) => Tag::fromArray($tag),
                $data['tags']
            );
        }

        $books = null;
        if (isset($data['books']) && is_array($data['books'])) {
            $books = array_map(
                fn (array $book) => Book::fromArray($book),
                $data['books']
            );
        }

        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            description: $data['description'] ?? null,
            descriptionHtml: $data['description_html'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            createdBy: isset($data['created_by'])
                ? (is_array($data['created_by']) ? (int) $data['created_by']['id'] : (int) $data['created_by'])
                : null,
            updatedBy: isset($data['updated_by'])
                ? (is_array($data['updated_by']) ? (int) $data['updated_by']['id'] : (int) $data['updated_by'])
                : null,
            ownedBy: isset($data['owned_by'])
                ? (is_array($data['owned_by']) ? (int) $data['owned_by']['id'] : (int) $data['owned_by'])
                : null,
            tags: $tags,
            cover: Cover::fromArray($data['cover'] ?? null),
            books: $books
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
            'slug' => $this->slug,
            'description' => $this->description,
            'description_html' => $this->descriptionHtml,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'owned_by' => $this->ownedBy,
            'tags' => null !== $this->tags
                ? array_map(fn (Tag $tag) => $tag->toArray(), $this->tags)
                : null,
            'cover' => $this->cover?->toArray(),
            'books' => null !== $this->books
                ? array_map(fn (Book $book) => $book->toArray(), $this->books)
                : null,
        ], static fn ($value): bool => null !== $value);
    }
}

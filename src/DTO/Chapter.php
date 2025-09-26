<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Chapter data transfer object.
 *
 * @author Camille Islasse
 */
readonly class Chapter
{
    /**
     * @param array<Tag>|null         $tags
     * @param array<PageSummary>|null $pages
     */
    public function __construct(
        public int $id,
        public int $bookId,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $descriptionHtml = null,
        public int $priority = 0,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
        public ?int $ownedBy = null,
        public ?int $defaultTemplateId = null,
        public ?string $bookSlug = null,
        public ?array $tags = null,
        public ?array $pages = null,
    ) {
    }

    /**
     * Create Chapter from API response array.
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

        $pages = null;
        if (isset($data['pages']) && is_array($data['pages'])) {
            $pages = array_map(
                fn (array $page) => PageSummary::fromArray($page),
                $data['pages']
            );
        }

        return new self(
            id: (int) $data['id'],
            bookId: (int) $data['book_id'],
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            description: $data['description'] ?? null,
            descriptionHtml: $data['description_html'] ?? null,
            priority: (int) ($data['priority'] ?? 0),
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
            defaultTemplateId: isset($data['default_template_id']) ? (int) $data['default_template_id'] : null,
            bookSlug: $data['book_slug'] ?? null,
            tags: $tags,
            pages: $pages
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
            'book_id' => $this->bookId,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'description_html' => $this->descriptionHtml,
            'priority' => $this->priority,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'owned_by' => $this->ownedBy,
            'default_template_id' => $this->defaultTemplateId,
            'book_slug' => $this->bookSlug,
            'tags' => null !== $this->tags
                ? array_map(fn (Tag $tag) => $tag->toArray(), $this->tags)
                : null,
            'pages' => null !== $this->pages
                ? array_map(fn (PageSummary $page) => $page->toArray(), $this->pages)
                : null,
        ], static fn ($value): bool => null !== $value);
    }
}

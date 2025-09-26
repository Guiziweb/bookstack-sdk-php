<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Page data transfer object.
 *
 * @author Camille Islasse
 */
readonly class Page
{
    /**
     * @param array<Tag>|null $tags
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public int $bookId,
        public ?int $chapterId = null,
        public ?string $html = null,
        public ?string $rawHtml = null,
        public ?string $markdown = null,
        public int $priority = 0,
        public bool $draft = false,
        public bool $template = false,
        public int $revisionCount = 0,
        public ?string $editor = null,
        public ?string $bookSlug = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
        public ?int $ownedBy = null,
        public ?array $tags = null,
    ) {
    }

    /**
     * Create Page from API response array.
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

        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            bookId: (int) $data['book_id'],
            chapterId: isset($data['chapter_id']) ? (int) $data['chapter_id'] : null,
            html: $data['html'] ?? null,
            rawHtml: $data['raw_html'] ?? null,
            markdown: $data['markdown'] ?? null,
            priority: (int) ($data['priority'] ?? 0),
            draft: (bool) ($data['draft'] ?? false),
            template: (bool) ($data['template'] ?? false),
            revisionCount: (int) ($data['revision_count'] ?? 0),
            editor: $data['editor'] ?? null,
            bookSlug: $data['book_slug'] ?? null,
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
            tags: $tags
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
            'book_id' => $this->bookId,
            'chapter_id' => $this->chapterId,
            'html' => $this->html,
            'raw_html' => $this->rawHtml,
            'markdown' => $this->markdown,
            'priority' => $this->priority,
            'draft' => $this->draft,
            'template' => $this->template,
            'revision_count' => $this->revisionCount,
            'editor' => $this->editor,
            'book_slug' => $this->bookSlug,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'owned_by' => $this->ownedBy,
            'tags' => null !== $this->tags
                ? array_map(fn (Tag $tag) => $tag->toArray(), $this->tags)
                : null,
        ], static fn ($value): bool => null !== $value);
    }
}

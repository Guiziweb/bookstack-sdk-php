<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Book content item data transfer object (chapters and pages).
 *
 * @author Camille Islasse
 */
readonly class BookContent
{
    /**
     * @param array<PageSummary>|null $pages
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $type, // 'chapter' or 'page'
        public int $bookId,
        public ?int $chapterId = null,
        public ?string $url = null,
        public ?array $pages = null,
    ) {
    }

    /**
     * Create BookContent from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $pages = null;
        if (isset($data['pages']) && is_array($data['pages'])) {
            $pages = array_map(
                fn (array $page) => PageSummary::fromArray($page),
                $data['pages']
            );
        }

        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            type: (string) $data['type'],
            bookId: (int) $data['book_id'],
            chapterId: isset($data['chapter_id']) ? (int) $data['chapter_id'] : null,
            url: $data['url'] ?? null,
            pages: $pages
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
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'book_id' => $this->bookId,
            'chapter_id' => $this->chapterId,
            'url' => $this->url,
            'pages' => null !== $this->pages
                ? array_map(fn (PageSummary $page) => $page->toArray(), $this->pages)
                : null,
        ], static fn ($value): bool => null !== $value);
    }
}

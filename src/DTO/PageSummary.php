<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Page summary data transfer object (minimal page info).
 *
 * @author Camille Islasse
 */
readonly class PageSummary
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public bool $draft = false,
        public bool $template = false,
    ) {
    }

    /**
     * Create PageSummary from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            draft: (bool) ($data['draft'] ?? false),
            template: (bool) ($data['template'] ?? false)
        );
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'draft' => $this->draft,
            'template' => $this->template,
        ];
    }
}

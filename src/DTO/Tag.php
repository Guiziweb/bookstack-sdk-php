<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * Tag data transfer object.
 *
 * @author Camille Islasse
 */
readonly class Tag
{
    public function __construct(
        public string $name,
        public string $value,
        public int $order = 0,
    ) {
    }

    /**
     * Create Tag from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            value: (string) $data['value'],
            order: (int) ($data['order'] ?? 0)
        );
    }

    /**
     * Convert to array for API requests.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'order' => $this->order,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * FallbackPermissions data transfer object.
 *
 * @author Camille Islasse
 */
readonly class FallbackPermissions
{
    public function __construct(
        public bool $inheriting,
        public bool $view,
        public bool $create,
        public bool $update,
        public bool $delete,
    ) {
    }

    /**
     * Create FallbackPermissions from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            inheriting: (bool) $data['inheriting'],
            view: (bool) $data['view'],
            create: (bool) $data['create'],
            update: (bool) $data['update'],
            delete: (bool) $data['delete']
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
            'inheriting' => $this->inheriting,
            'view' => $this->view,
            'create' => $this->create,
            'update' => $this->update,
            'delete' => $this->delete,
        ];
    }
}

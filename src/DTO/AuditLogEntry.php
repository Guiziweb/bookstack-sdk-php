<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\DTO;

/**
 * AuditLogEntry data transfer object.
 *
 * @author Camille Islasse
 */
readonly class AuditLogEntry
{
    public function __construct(
        public int $id,
        public string $type,
        public string $detail,
        public int $userId,
        public ?int $loggableId,
        public ?string $loggableType,
        public string $ip,
        public string $createdAt,
        public ?AuditUser $user = null,
    ) {
    }

    /**
     * Create AuditLogEntry from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            type: (string) $data['type'],
            detail: (string) $data['detail'],
            userId: (int) $data['user_id'],
            loggableId: isset($data['loggable_id']) ? (int) $data['loggable_id'] : null,
            loggableType: isset($data['loggable_type']) ? (string) $data['loggable_type'] : null,
            ip: (string) $data['ip'],
            createdAt: (string) $data['created_at'],
            user: isset($data['user']) && is_array($data['user'])
                ? AuditUser::fromArray($data['user'])
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
            'type' => $this->type,
            'detail' => $this->detail,
            'user_id' => $this->userId,
            'loggable_id' => $this->loggableId,
            'loggable_type' => $this->loggableType,
            'ip' => $this->ip,
            'created_at' => $this->createdAt,
            'user' => $this->user?->toArray(),
        ], static fn ($value): bool => null !== $value);
    }
}

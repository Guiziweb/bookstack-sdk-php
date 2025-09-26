<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\AuditLogEntry;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack audit logs.
 *
 * @author Camille Islasse
 */
class AuditLogService extends AbstractService
{
    /**
     * Get audit log entries.
     *
     * @param array<string, string> $sort
     * @param array<string, mixed>  $filter
     *
     * @return array{data: array<int, AuditLogEntry>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = [], array $filter = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];

        $query = array_merge($query, $this->transformSortParameters($sort));

        if (!empty($filter)) {
            $query['filter'] = $filter;
        }

        $response = $this->get('/api/audit-log', $query);

        return [
            'data' => array_map(
                fn (array $auditData) => AuditLogEntry::fromArray($auditData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }
}

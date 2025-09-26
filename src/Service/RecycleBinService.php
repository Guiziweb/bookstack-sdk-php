<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\RecycleBinItem;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack recycle bin.
 *
 * @author Camille Islasse
 */
class RecycleBinService extends AbstractService
{
    /**
     * List items in the recycle bin.
     *
     * @param array<string, string> $sort
     *
     * @return array{data: array<int, RecycleBinItem>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];

        $query = array_merge($query, $this->transformSortParameters($sort));

        $response = $this->get('/api/recycle-bin', $query);

        return [
            'data' => array_map(
                fn (array $itemData) => RecycleBinItem::fromArray($itemData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Restore an item from the recycle bin.
     */
    public function restore(int $id): RecycleBinItem
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->put("/api/recycle-bin/{$id}");

        return RecycleBinItem::fromArray($response);
    }

    /**
     * Permanently delete an item from the recycle bin.
     */
    public function destroy(int $id): void
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $this->deleteRequest("/api/recycle-bin/{$id}");
    }
}

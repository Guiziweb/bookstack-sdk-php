<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\Image;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack image gallery.
 *
 * @author Camille Islasse
 */
class ImageService extends AbstractService
{
    /**
     * List all images in gallery.
     *
     * @param array<string, string> $sort
     *
     * @return array{data: array<int, Image>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];

        $query = array_merge($query, $this->transformSortParameters($sort));

        $response = $this->get('/api/image-gallery', $query);

        return [
            'data' => array_map(
                fn (array $imageData) => Image::fromArray($imageData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Create/upload a new image.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Image
    {
        ParameterValidator::validateRequiredFields($data, ['type', 'uploaded_to']);

        $response = $this->post('/api/image-gallery', $data);

        return Image::fromArray($response);
    }

    /**
     * Get a specific image by ID.
     */
    public function show(int $id): Image
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->get("/api/image-gallery/{$id}");

        return Image::fromArray($response);
    }

    /**
     * Update a specific image.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Image
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->put("/api/image-gallery/{$id}", $data);

        return Image::fromArray($response);
    }

    /**
     * Delete a specific image.
     */
    public function delete(int $id): void
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $this->deleteRequest("/api/image-gallery/{$id}");
    }
}

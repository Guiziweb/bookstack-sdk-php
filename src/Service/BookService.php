<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Service;

use Guiziweb\BookStackClient\DTO\Book;
use Guiziweb\BookStackClient\DTO\BookContent;
use Guiziweb\BookStackClient\Validator\ParameterValidator;

/**
 * Service for managing BookStack books.
 *
 * @author Camille Islasse
 */
class BookService extends AbstractService
{
    /**
     * List all books.
     *
     * @param array<string, string> $sort
     *
     * @return array{data: array<int, Book>, total: int}
     */
    public function list(int $count = 100, int $offset = 0, array $sort = []): array
    {
        ParameterValidator::validateCount($count);
        ParameterValidator::validateNonNegativeInteger($offset, 'offset');

        $query = ['count' => $count, 'offset' => $offset];
        $query = array_merge($query, $this->transformSortParameters($sort));

        $response = $this->get('/api/books', $query);

        return [
            'data' => array_map(
                fn (array $bookData) => Book::fromArray($bookData),
                $response['data'] ?? []
            ),
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Create a new book.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Book
    {
        ParameterValidator::validateRequiredFields($data, ['name']);

        $response = $this->post('/api/books', $data);

        return Book::fromArray($response);
    }

    /**
     * Get a specific book by ID.
     */
    public function show(int $id): Book
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->get("/api/books/{$id}");

        return Book::fromArray($response);
    }

    /**
     * Update a specific book.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Book
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->put("/api/books/{$id}", $data);

        return Book::fromArray($response);
    }

    /**
     * Delete a specific book.
     */
    public function delete(int $id): void
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $this->deleteRequest("/api/books/{$id}");
    }

    /**
     * Get the content of a book.
     *
     * @return array<BookContent>
     */
    public function contents(int $id): array
    {
        ParameterValidator::validatePositiveInteger($id, 'id');

        $response = $this->get("/api/books/{$id}/contents");

        return array_map(
            fn (array $contentData) => BookContent::fromArray($contentData),
            $response['contents'] ?? []
        );
    }

    /**
     * Export a book in the specified format.
     */
    public function export(int $id, string $format = 'pdf'): string
    {
        ParameterValidator::validatePositiveInteger($id, 'id');
        ParameterValidator::validateExportFormat($format);

        return $this->getRaw("/api/books/{$id}/export/{$format}");
    }
}

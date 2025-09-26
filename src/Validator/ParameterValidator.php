<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Validator;

use Guiziweb\BookStackClient\Exception\ValidationException;

/**
 * Parameter validation helper.
 *
 * @author Camille Islasse
 */
class ParameterValidator
{
    private const EXPORT_FORMATS = ['pdf', 'html', 'plaintext', 'markdown'];
    private const SEARCH_TYPES = ['book', 'page', 'chapter', 'bookshelf'];

    public static function validateExportFormat(string $format): void
    {
        if (!in_array($format, self::EXPORT_FORMATS, true)) {
            throw new ValidationException(sprintf('Invalid export format "%s". Allowed formats: %s', $format, implode(', ', self::EXPORT_FORMATS)));
        }
    }

    /**
     * @param array<int, string> $types
     */
    public static function validateSearchTypes(array $types): void
    {
        foreach ($types as $type) {
            if (!in_array($type, self::SEARCH_TYPES, true)) {
                throw new ValidationException(sprintf('Invalid search type "%s". Allowed types: %s', $type, implode(', ', self::SEARCH_TYPES)));
            }
        }
    }

    public static function validatePositiveInteger(int $value, string $parameterName): void
    {
        if ($value <= 0) {
            throw new ValidationException(sprintf('Parameter "%s" must be a positive integer, got: %d', $parameterName, $value));
        }
    }

    public static function validateNonNegativeInteger(int $value, string $parameterName): void
    {
        if ($value < 0) {
            throw new ValidationException(sprintf('Parameter "%s" must be a non-negative integer, got: %d', $parameterName, $value));
        }
    }

    public static function validateCount(int $count): void
    {
        if ($count < 1 || $count > 500) {
            throw new ValidationException(sprintf('Count must be between 1 and 500, got: %d', $count));
        }
    }

    public static function validateRequiredString(string $value, string $parameterName): void
    {
        if ('' === trim($value)) {
            throw new ValidationException(sprintf('Parameter "%s" cannot be empty', $parameterName));
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string>        $requiredFields
     */
    public static function validateRequiredFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data) || null === $data[$field] || '' === $data[$field]) {
                throw new ValidationException(sprintf('Required field "%s" is missing or empty', $field));
            }
        }
    }
}

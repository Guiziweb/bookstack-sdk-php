<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Unit\Validator;

use Guiziweb\BookStackClient\Exception\ValidationException;
use Guiziweb\BookStackClient\Validator\ParameterValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ParameterValidator.
 *
 * @author Camille Islasse
 */
class ParameterValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function itValidatesExportFormats(): void
    {
        // Valid formats should not throw
        ParameterValidator::validateExportFormat('pdf');
        ParameterValidator::validateExportFormat('html');
        ParameterValidator::validateExportFormat('plaintext');
        ParameterValidator::validateExportFormat('markdown');
        // ParameterValidator::validateExportFormat('zip'); // Requires BookStack v25.05+

        $this->assertTrue(true); // If we reach here, no exceptions were thrown
    }

    /**
     * @test
     */
    public function itThrowsForInvalidExportFormat(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid export format "invalid". Allowed formats: pdf, html, plaintext, markdown');

        ParameterValidator::validateExportFormat('invalid');
    }

    /**
     * @test
     */
    public function itValidatesSearchTypes(): void
    {
        // Valid single types
        ParameterValidator::validateSearchTypes(['book']);
        ParameterValidator::validateSearchTypes(['page']);
        ParameterValidator::validateSearchTypes(['chapter']);
        ParameterValidator::validateSearchTypes(['bookshelf']);

        // Valid multiple types
        ParameterValidator::validateSearchTypes(['book', 'page']);
        ParameterValidator::validateSearchTypes(['book', 'page', 'chapter', 'bookshelf']);

        $this->assertTrue(true); // If we reach here, no exceptions were thrown
    }

    /**
     * @test
     */
    public function itThrowsForInvalidSearchType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid search type "invalid". Allowed types: book, page, chapter, bookshelf');

        ParameterValidator::validateSearchTypes(['book', 'invalid', 'page']);
    }

    /**
     * @test
     */
    public function itValidatesPositiveIntegers(): void
    {
        // Valid positive integers
        ParameterValidator::validatePositiveInteger(1, 'test');
        ParameterValidator::validatePositiveInteger(42, 'test');
        ParameterValidator::validatePositiveInteger(999999, 'test');

        $this->assertTrue(true); // If we reach here, no exceptions were thrown
    }

    /**
     * @test
     */
    public function itThrowsForZeroAsPositiveInteger(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Parameter "id" must be a positive integer, got: 0');

        ParameterValidator::validatePositiveInteger(0, 'id');
    }

    /**
     * @test
     */
    public function itThrowsForNegativeAsPositiveInteger(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Parameter "count" must be a positive integer, got: -5');

        ParameterValidator::validatePositiveInteger(-5, 'count');
    }

    /**
     * @test
     */
    public function itValidatesNonNegativeIntegers(): void
    {
        // Valid non-negative integers
        ParameterValidator::validateNonNegativeInteger(0, 'test');
        ParameterValidator::validateNonNegativeInteger(1, 'test');
        ParameterValidator::validateNonNegativeInteger(100, 'test');

        $this->assertTrue(true); // If we reach here, no exceptions were thrown
    }

    /**
     * @test
     */
    public function itThrowsForNegativeAsNonNegativeInteger(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Parameter "offset" must be a non-negative integer, got: -1');

        ParameterValidator::validateNonNegativeInteger(-1, 'offset');
    }

    /**
     * @test
     */
    public function itValidatesCount(): void
    {
        // Valid counts
        ParameterValidator::validateCount(1);
        ParameterValidator::validateCount(50);
        ParameterValidator::validateCount(100);
        ParameterValidator::validateCount(500);

        $this->assertTrue(true); // If we reach here, no exceptions were thrown
    }

    /**
     * @test
     */
    public function itThrowsForCountTooLow(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Count must be between 1 and 500, got: 0');

        ParameterValidator::validateCount(0);
    }

    /**
     * @test
     */
    public function itThrowsForCountTooHigh(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Count must be between 1 and 500, got: 501');

        ParameterValidator::validateCount(501);
    }

    /**
     * @test
     */
    public function itValidatesRequiredString(): void
    {
        // Valid strings
        ParameterValidator::validateRequiredString('valid string', 'test');
        ParameterValidator::validateRequiredString('  trimmed  ', 'test');

        $this->assertTrue(true); // If we reach here, no exceptions were thrown
    }

    /**
     * @test
     */
    public function itThrowsForEmptyRequiredString(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Parameter "name" cannot be empty');

        ParameterValidator::validateRequiredString('', 'name');
    }

    /**
     * @test
     */
    public function itThrowsForWhitespaceOnlyRequiredString(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Parameter "title" cannot be empty');

        ParameterValidator::validateRequiredString('   ', 'title');
    }

    /**
     * @test
     */
    public function itValidatesRequiredFields(): void
    {
        $data = [
            'name' => 'Test Name',
            'email' => 'test@example.com',
            'description' => 'Some description',
        ];

        // Valid data with all required fields
        ParameterValidator::validateRequiredFields($data, ['name', 'email']);
        ParameterValidator::validateRequiredFields($data, ['name']);
        ParameterValidator::validateRequiredFields($data, []);

        $this->assertTrue(true); // If we reach here, no exceptions were thrown
    }

    /**
     * @test
     */
    public function itThrowsForMissingRequiredField(): void
    {
        $data = [
            'name' => 'Test Name',
            'description' => 'Some description',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Required field "email" is missing or empty');

        ParameterValidator::validateRequiredFields($data, ['name', 'email']);
    }

    /**
     * @test
     */
    public function itThrowsForNullRequiredField(): void
    {
        $data = [
            'name' => 'Test Name',
            'email' => null,
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Required field "email" is missing or empty');

        ParameterValidator::validateRequiredFields($data, ['name', 'email']);
    }

    /**
     * @test
     */
    public function itThrowsForEmptyStringRequiredField(): void
    {
        $data = [
            'name' => 'Test Name',
            'email' => '',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Required field "email" is missing or empty');

        ParameterValidator::validateRequiredFields($data, ['name', 'email']);
    }
}

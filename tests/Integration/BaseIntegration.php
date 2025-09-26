<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\BookStackClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * Base class for all integration tests.
 * Handles client setup and common functionality.
 *
 * @author Camille Islasse
 */
abstract class BaseIntegration extends TestCase
{
    protected \Guiziweb\BookStackClient\BookStackClient $client;

    protected function setUp(): void
    {
        // Load .env file if it exists
        $this->loadEnvFile();

        $baseUrl = $_ENV['BOOKSTACK_BASE_URL'] ?? null;
        $apiKey = $_ENV['BOOKSTACK_API_KEY'] ?? null;
        $apiSecret = $_ENV['BOOKSTACK_API_SECRET'] ?? null;

        if (!$baseUrl || !$apiKey || !$apiSecret) {
            $this->markTestSkipped('BookStack credentials not configured');
        }

        $this->client = BookStackClientFactory::create($baseUrl, $apiKey, $apiSecret);
    }

    private function loadEnvFile(): void
    {
        $envFile = __DIR__.'/../../.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (false === $lines) {
                return;
            }

            foreach ($lines as $line) {
                $line = trim($line);

                if (empty($line) || 0 === strpos($line, '#')) {
                    continue;
                }

                if (false !== strpos($line, '=')) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value, '"\'');

                    if (!isset($_ENV[$name])) {
                        $_ENV[$name] = $value;
                    }
                }
            }
        }
    }

    /**
     * Helper to safely delete resources during cleanup.
     */
    protected function safeDelete(callable $deleteCallback, string $resourceType, int $resourceId): void
    {
        try {
            $deleteCallback();
        } catch (\Exception $e) {
            echo "Failed to cleanup $resourceType $resourceId: ".$e->getMessage()."\n";
        }
    }

    /**
     * Add delay between API calls to avoid rate limiting.
     */
    protected function addDelay(): void
    {
        $delay = (int) ($_ENV['BOOKSTACK_TEST_DELAY'] ?? 100);
        if ($delay > 0) {
            usleep($delay * 1000); // Convert ms to microseconds
        }
    }

    /**
     * Make API call with automatic delay.
     *
     * @template T
     *
     * @param callable(): T $apiCall
     *
     * @return T
     */
    protected function makeApiCall(callable $apiCall)
    {
        $this->addDelay();

        return $apiCall();
    }
}

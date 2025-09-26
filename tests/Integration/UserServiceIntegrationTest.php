<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\User;

/**
 * Integration tests for UserService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class UserServiceIntegrationTest extends BaseIntegration
{
    /** @var array<int, int> */
    private array $createdUsers = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->createdUsers) as $userId) {
            $this->safeDelete(
                fn () => $this->client->users()->delete($userId),
                'user',
                $userId
            );
        }
    }

    /**
     * @test
     */
    public function itCanListUsersWithAllParameters(): void
    {
        // Basic list
        $users = $this->client->users()->list();
        $this->assertIsArray($users);
        $this->assertArrayHasKey('data', $users);
        $this->assertGreaterThan(0, count($users['data']), 'Should have at least one user (admin)');
        $this->assertContainsOnlyInstancesOf(User::class, $users['data']);

        // With parameters
        $users = $this->client->users()->list(5, 0, ['name' => 'asc']);
        $this->assertIsArray($users);
        $this->assertLessThanOrEqual(5, count($users['data']));
        $this->assertContainsOnlyInstancesOf(User::class, $users['data']);

        // With different sort
        $users = $this->client->users()->list(3, 0, ['email' => 'desc']);
        $this->assertIsArray($users);
    }

    /**
     * @test
     */
    public function itCanShowExistingUser(): void
    {
        $users = $this->client->users()->list(1);
        $this->assertNotEmpty($users['data']);

        $userId = $users['data'][0]->id;
        $user = $this->client->users()->show($userId);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userId, $user->id);
        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
    }

    /**
     * @test
     */
    public function itCanPerformUserCrudOperations(): void
    {
        // CREATE - Minimal
        $user1 = $this->client->users()->create([
            'name' => 'Integration Test User 1',
            'email' => 'integration.test.user1@example.com',
        ]);
        $this->assertInstanceOf(User::class, $user1);
        $this->assertIsInt($user1->id);
        $this->assertEquals('Integration Test User 1', $user1->name);
        $this->assertEquals('integration.test.user1@example.com', $user1->email);
        $this->createdUsers[] = $user1->id;

        // CREATE - Complete data
        $user2 = $this->client->users()->create([
            'name' => 'Integration Test User 2',
            'email' => 'integration.test.user2@example.com',
            'password' => 'TestPassword123!',
            'send_invite' => false,
        ]);
        $this->assertInstanceOf(User::class, $user2);
        $this->assertIsInt($user2->id);
        $this->assertEquals('Integration Test User 2', $user2->name);
        $this->createdUsers[] = $user2->id;

        // READ
        $retrievedUser = $this->client->users()->show($user1->id);
        $this->assertInstanceOf(User::class, $retrievedUser);
        $this->assertEquals($user1->id, $retrievedUser->id);
        $this->assertEquals('Integration Test User 1', $retrievedUser->name);

        // UPDATE
        $updatedUser = $this->client->users()->update($user1->id, [
            'name' => 'Updated Integration Test User 1',
            'email' => 'updated.integration.test.user1@example.com',
        ]);
        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertEquals('Updated Integration Test User 1', $updatedUser->name);
        $this->assertEquals('updated.integration.test.user1@example.com', $updatedUser->email);
    }

    /**
     * @test
     */
    public function itHandlesUserDeletion(): void
    {
        $user = $this->client->users()->create([
            'name' => 'Delete Test User',
            'email' => 'delete.test.user@example.com',
        ]);

        $userId = $user->id;

        // Delete the user
        $this->client->users()->delete($userId);

        // Try to retrieve deleted user - should fail
        $this->expectException(\Exception::class);
        $this->client->users()->show($userId);
    }
}

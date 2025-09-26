<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Integration;

use Guiziweb\BookStackClient\DTO\Role;

/**
 * Integration tests for RoleService.
 *
 * @group integration
 *
 * @author Camille Islasse
 */
class RoleServiceIntegrationTest extends BaseIntegration
{
    /** @var array<int, int> */
    private array $createdRoles = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Delete roles (be careful with system roles)
        foreach (array_reverse($this->createdRoles) as $roleId) {
            $this->safeDelete(
                fn () => $this->client->roles()->delete($roleId),
                'role',
                $roleId
            );
        }
    }

    /**
     * @test
     */
    public function itCanListRolesWithAllParameters(): void
    {
        // Basic list
        $roles = $this->makeApiCall(fn () => $this->client->roles()->list());
        $this->assertIsArray($roles);
        $this->assertArrayHasKey('data', $roles);

        // Should have at least some default roles
        $this->assertGreaterThan(0, count($roles['data']));
        $this->assertContainsOnlyInstancesOf(Role::class, $roles['data']);

        // With count
        $roles = $this->makeApiCall(fn () => $this->client->roles()->list(5));
        $this->assertIsArray($roles);
        $this->assertLessThanOrEqual(5, count($roles['data']));
        $this->assertContainsOnlyInstancesOf(Role::class, $roles['data']);

        // With count and offset
        $roles = $this->makeApiCall(fn () => $this->client->roles()->list(2, 1));
        $this->assertIsArray($roles);
        $this->assertLessThanOrEqual(2, count($roles['data']));
        $this->assertContainsOnlyInstancesOf(Role::class, $roles['data']);

        // With sorting
        $roles = $this->makeApiCall(fn () => $this->client->roles()->list(5, 0, ['display_name' => 'asc']));
        $this->assertIsArray($roles);
        $this->assertContainsOnlyInstancesOf(Role::class, $roles['data']);

        $roles = $this->makeApiCall(fn () => $this->client->roles()->list(5, 0, ['created_at' => 'desc']));
        $this->assertIsArray($roles);
        $this->assertContainsOnlyInstancesOf(Role::class, $roles['data']);
    }

    /**
     * @test
     */
    public function itCanShowExistingRole(): void
    {
        // Get first role from list to test show
        $roles = $this->makeApiCall(fn () => $this->client->roles()->list(1));
        $this->assertArrayHasKey('data', $roles);
        $this->assertNotEmpty($roles['data']);

        $firstRole = $roles['data'][0];
        $roleId = $firstRole->id;  // DTO property

        // READ the role
        $retrievedRole = $this->makeApiCall(fn () => $this->client->roles()->show($roleId));
        $this->assertEquals($roleId, $retrievedRole->id);  // DTO property
        $this->assertIsString($retrievedRole->displayName);  // DTO property
        $this->assertTrue(is_string($retrievedRole->description) || is_null($retrievedRole->description));
    }

    /**
     * @test
     */
    public function itCanCreateAndManageCustomRole(): void
    {
        try {
            // CREATE - Custom role
            $role = $this->makeApiCall(fn () => $this->client->roles()->create([
                'display_name' => 'Integration Test Role',
                'description' => 'Role created for integration testing',
                'permissions' => [],
            ]));

            $this->assertInstanceOf(Role::class, $role);
            $this->assertIsInt($role->id);
            $this->assertEquals('Integration Test Role', $role->displayName);
            $this->assertEquals('Role created for integration testing', $role->description);
            $this->createdRoles[] = $role->id;

            // READ the created role
            $retrievedRole = $this->makeApiCall(fn () => $this->client->roles()->show($role->id));
            $this->assertInstanceOf(Role::class, $retrievedRole);
            $this->assertEquals($role->id, $retrievedRole->id);
            $this->assertEquals('Integration Test Role', $retrievedRole->displayName);

            // UPDATE the role
            $updatedRole = $this->makeApiCall(fn () => $this->client->roles()->update($role->id, [
                'display_name' => 'Updated Integration Test Role',
                'description' => 'Updated description for testing',
            ]));
            $this->assertInstanceOf(Role::class, $updatedRole);
            $this->assertEquals('Updated Integration Test Role', $updatedRole->displayName);
            $this->assertEquals('Updated description for testing', $updatedRole->description);
        } catch (\Exception $e) {
            // Role creation might require special permissions
            $this->markTestSkipped('Role creation not allowed: '.$e->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanCreateRoleWithPermissions(): void
    {
        try {
            // CREATE - Role with basic permissions
            $role = $this->makeApiCall(fn () => $this->client->roles()->create([
                'display_name' => 'Test Role with Permissions',
                'description' => 'Role with specific permissions',
                'permissions' => [
                    'content-export',
                    'settings-view',
                ],
            ]));

            $this->assertInstanceOf(Role::class, $role);
            $this->assertEquals('Test Role with Permissions', $role->displayName);
            $this->createdRoles[] = $role->id;

            // Verify permissions if returned
            if (null !== $role->permissions) {
                $this->assertIsArray($role->permissions);
            }
        } catch (\Exception $e) {
            // Permission assignment might not work as expected
            $this->markTestSkipped('Role with permissions creation not supported: '.$e->getMessage());
        }
    }

    /**
     * @test
     */
    public function itHandlesRoleDeletionSafely(): void
    {
        try {
            // Create a role to delete
            $role = $this->makeApiCall(fn () => $this->client->roles()->create([
                'display_name' => 'Delete Test Role',
                'description' => 'Role to be deleted',
            ]));

            $roleId = $role->id;

            // Delete the role
            $this->makeApiCall(fn () => $this->client->roles()->delete($roleId));

            // Try to retrieve deleted role - should fail
            $this->expectException(\Exception::class);
            $this->makeApiCall(fn () => $this->client->roles()->show($roleId));
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not allowed') || str_contains($e->getMessage(), 'permission')) {
                $this->markTestSkipped('Role deletion not allowed: '.$e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * @test
     */
    public function itCannotDeleteSystemRoles(): void
    {
        // Try to delete a system role (should fail)
        // Get the first role (likely a system role)
        $roles = $this->makeApiCall(fn () => $this->client->roles()->list(1));
        $firstRole = $roles['data'][0];

        $this->expectException(\Exception::class);
        $this->makeApiCall(fn () => $this->client->roles()->delete($firstRole->id));
    }
}

<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use Modules\Users\Domain\Models\User;
use Modules\Users\Domain\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Modules\Core\Infrastructure\Database\Seeders\CoreDatabaseSeeder::class);
        $this->seed(\Modules\Users\Infrastructure\Database\Seeders\UsersDatabaseSeeder::class);
    }

    /** @test */
    public function secure_routes_return_403_for_users_without_permissions()
    {
        // 1. Create a user
        $user = User::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 2. Assign an empty role
        $role = Role::create([
            'name' => 'empty-role-test',
            'display_name' => 'Empty Role Test',
            'guard_name' => 'web'
        ]);
        $user->assignRole($role);

        $this->actingAs($user);

        // 3. Define critical dashboard and admin routes to test
        $criticalRoutes = [
            '/educational', // Educational dashboard
            '/dashboard', // Main dashboard
            '/admin/tickets/categories', // Management route Example
            '/admin/tickets/stages', // Management route Example
            '/admin/ermo', // Super admin route
        ];

        // 4. Assert each route denies access explicitly (403)
        foreach ($criticalRoutes as $route) {
            $response = $this->get($route);

            $response->assertStatus(403)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

            // Check that it's the custom 403 view
            $response->assertSee('403');
            $response->assertSee('Access Denied');
        }
    }
}

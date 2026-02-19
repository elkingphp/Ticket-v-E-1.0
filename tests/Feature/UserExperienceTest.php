<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Modules\Users\Domain\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic roles and permissions using the modular seeder
        $this->seed('Modules\Core\Infrastructure\Database\Seeders\RolesAndPermissionsSeeder');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function super_admin_can_see_all_menu_items()
    {
        $admin = User::factory()->create([
            'two_factor_secret' => 'secret', // Essential for super-admin as per TwoFactorMandatory
        ]);
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Users Management');
        $response->assertSee('System Settings');
        $response->assertSee('Audit Logs');

        // Phase 4: Verify Widgets visibility
        $response->assertSee('Profile Integrity');
        $response->assertSee('Activity Trend');
        $response->assertSee('User Growth');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function restricted_user_cannot_see_unauthorized_menu_items()
    {
        $user = User::factory()->create();
        // Give basic role that doesn't have system permissions
        $role = Role::create(['name' => 'editor']);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('t-user-list'); // Testing by data-key or specific link text
        $response->assertDontSee('t-settings');
        $response->assertDontSee('t-audit');

        // Phase 4: Verify Widgets are hidden for restricted users
        $response->assertDontSee('Profile Integrity');
        $response->assertDontSee('Activity Trend');
        $response->assertDontSee('User Growth');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unauthorized_access_is_handled_with_error_message()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $user->assignRole($role);

        // Try to access users list via URL
        $response = $this->actingAs($user)->from('/dashboard')->get('/audit-logs');

        // Should redirect back to dashboard with error
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error', 'You do not have permission to access this page.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_update_avatar_via_ajax()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $user->givePermissionTo('update profile');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->postJson(route('profile.avatar'), [
            'avatar' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_without_permission_cannot_update_profile()
    {
        $user = User::factory()->create();
        // No permissions given

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'first_name' => 'New Name',
            'last_name' => 'User',
            'email' => $user->email,
            'language' => 'en',
            'timezone' => 'UTC'
        ]);

        $response->assertSessionHas('error', 'You do not have permission to access this page.');
    }
}
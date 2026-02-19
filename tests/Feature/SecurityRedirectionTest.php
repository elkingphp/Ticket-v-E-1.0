<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Users\Domain\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityRedirectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'user', 'guard_name' => 'web']);
    }

    public function test_super_admin_can_bypass_profile_and_2fa_enforcement()
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->create([
            'email' => 'super@example.com',
            'phone' => null, // Incomplete profile
            'avatar' => null, // Incomplete profile
            'two_factor_secret' => null, // 2FA not enabled
        ]);
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)
            ->get('/dashboard');

        // Should NOT be redirected to profile
        $response->assertStatus(200);
        $response->assertViewIs('core::dashboard');
    }

    public function test_admin_is_redirected_if_2fa_is_missing()
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'phone' => '123456789',
            'avatar' => 'avatar.png',
            'two_factor_secret' => null, // Missing 2FA
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get('/dashboard');

        // Should be redirected to profile
        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('error', 'يجب تفعيل التحقق الثنائي (2FA) للوصول إلى هذه الصفحة نظراً لصلاحياتك الحساسة.');
    }

    public function test_regular_user_is_redirected_if_profile_is_incomplete()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'phone' => null, // Missing phone
            'avatar' => null, // Missing avatar
        ]);
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->get('/dashboard');

        // Should be redirected to profile
        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('error', 'يرجى إكمال بيانات ملفك الشخصي (رقم الهاتف وصورة الحساب) للمتابعة.');
    }
}
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Users\Domain\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_switch_language_to_arabic()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('lang.switch', 'ar'));

        $response->assertRedirect();
        $this->assertEquals('ar', session('locale'));

        // Verify user profile is updated
        $this->assertEquals('ar', $user->fresh()->language);
    }

    public function test_interface_renders_rtl_for_arabic()
    {
        $user = User::factory()->create([
            'language' => 'ar',
            'phone' => '123456789',
            'avatar' => 'avatar.png',
            'email_verified_at' => now(),
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser_ar',
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('lang="ar"', false);
        $response->assertSee('app-rtl.min.css', false);
    }

    public function test_interface_renders_ltr_for_english()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser_en',
            'phone' => '987654321',
            'avatar' => 'avatar.png',
            'language' => 'en',
        ]);
        $this->get('/lang/en');
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('lang="en"', false);
        $response->assertSessionHas('locale', 'en');
    }

    /** @test */
    public function it_provides_correct_datatables_language_url_for_arabic()
    {
        $this->get('/lang/ar');

        $response = $this->get('/locale-debug');
        $response->assertStatus(200)
            ->assertJsonFragment(['dt_language_url' => asset('assets/json/datatable-ar.json')]);
    }
}
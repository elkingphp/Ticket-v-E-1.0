<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Users\Domain\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

class UserSystemIntegrityTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Test deep polymorphic and functional integrity of the unified User model.
     */
    /**
     * Test deep polymorphic and functional integrity of the unified User model.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function user_system_integrity(): void
    {
        // 1. Setup User
        $user = User::first() ?: User::factory()->create([
            'email' => 'test_audit@example.com',
            'username' => 'test_audit'
        ]);

        // 2. Test Notification Polymorphism (New & Legacy Read)
        // Simulate a legacy record in DB (as string)
        DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'LegacyNotification',
            'notifiable_type' => 'user', // We use alias now
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'Old Data']),
            'created_at' => now(),
        ]);

        $this->assertCount(1, $user->notifications, 'User should be able to read polymorphic notifications using alias');

        // 3. Test Role Assignment (Spatie Permission)
        $role = Role::firstOrCreate(['name' => 'test-integrity-role', 'guard_name' => 'web']);
        $user->assignRole($role);
        $this->assertTrue($user->hasRole('test-integrity-role'), 'Role assignment should work with unified model');

        // 4. Test Sanctum Token Issuance
        $token = $user->createToken('test_token')->plainTextToken;
        $this->assertNotEmpty($token, 'Sanctum token issuance should work');

        // Verify tokenable_type in DB
        $tokenRecord = DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->first();
        $this->assertNotNull($tokenRecord, 'Personal access token record should exist');
        if ($tokenRecord instanceof \stdClass) {
            $this->assertEquals('user', $tokenRecord->tokenable_type, 'Sanctum should use morphMap alias for tokens');
        }

        // Clean up
        DB::table('notifications')->where('data->message', 'Old Data')->delete();
        $user->removeRole($role);
        DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
    }
}
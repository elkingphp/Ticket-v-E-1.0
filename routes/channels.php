<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
 |--------------------------------------------------------------------------
 | Broadcast Channels
 |--------------------------------------------------------------------------
 |
 | SECURITY NOTICE:
 | - All channels MUST have strict authorization checks
 | - NEVER use wildcard channels like '{any}' with return true
 | - NEVER return true without proper validation
 | - Always validate user identity matches channel ID
 | - Log unauthorized access attempts for security monitoring
 |
 */

/**
 * User Private Channel - For real-time notifications
 */
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

/**
 * ERMO Infrastructure Channel - For real-time cluster updates
 * 
 * SECURITY:
 * - Only users with 'super-admin' role can subscribe
 */
Broadcast::channel('ermo.cluster', function ($user) {
    return $user->hasRole('super-admin');
});

/**
 * Legacy Channel - For backward compatibility
 * 
 * Channel: App.Models.User.{id}
 */
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    $userId = (int)$user->id;
    $channelId = (int)$id;

    $authorized = $userId === $channelId;

    if (!$authorized) {
        Log::warning('Unauthorized WebSocket channel access attempt (legacy)', [
            'user_id' => $userId,
            'attempted_channel' => "App.Models.User.{$channelId}",
            'ip' => request()->ip(),
        ]);
    }

    return $authorized;
});

/*
 |--------------------------------------------------------------------------
 | IMPORTANT SECURITY NOTES:
 |--------------------------------------------------------------------------
 |
 | ❌ NEVER DO THIS:
 | Broadcast::channel('{any}', function () {
 |     return true;  // ❌ CRITICAL SECURITY VULNERABILITY
 | });
 |
 | ❌ NEVER DO THIS:
 | Broadcast::channel('user.{id}', function () {
 |     return true;  // ❌ Allows anyone to subscribe to any channel
 | });
 |
 | ✅ ALWAYS DO THIS:
 | Broadcast::channel('user.{id}', function ($user, $id) {
 |     return (int) $user->id === (int) $id;  // ✅ Strict validation
 | });
 |
 | 🔐 FUTURE ENHANCEMENT (Enterprise Level):
 | Consider using UUID-based channels instead of numeric IDs:
 | - user.{uuid} instead of user.{id}
 | - UUIDs are not sequential and cannot be guessed
 | - Provides additional security layer
 |
 */
<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Emergency Bypass
     |--------------------------------------------------------------------------
     |
     | When set to true, all ERMO runtime gating (middleware) will be bypassed.
     | This is a safety valve for production issues. API and state machine
     | logic will remain active.
     |
     */
    'emergency_bypass' => env('ERMO_EMERGENCY_BYPASS', false),

    /*
     |--------------------------------------------------------------------------
     | Resilience Settings
     |--------------------------------------------------------------------------
     */
    'resilience' => [
        'redis_failover_ttl' => 30, // Seconds to stay in degraded mode if Redis fails

        'circuit_breaker' => [
            'failure_threshold' => 5, // Absolute failures
            'failure_ratio' => 0.5, // 50% failure rate
            'window_seconds' => 60, // Time window
        ],

        'load_shedding' => [
            'default_max_concurrent' => 100,
        ],
    ],
];
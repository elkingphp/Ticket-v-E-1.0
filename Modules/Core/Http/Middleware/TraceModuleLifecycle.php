<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\Core\Domain\Interfaces\ModuleManagerInterface;
use Modules\Core\Domain\Models\Module;
use Modules\Core\Domain\Models\ModuleRequestTrace;
use Illuminate\Support\Facades\{Log, Redis, Cache, DB};

class TraceModuleLifecycle
{
    protected static array $shaCache = [];
    protected ModuleManagerInterface $moduleManager;
    protected float $startTime;

    public function __construct(ModuleManagerInterface $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        $this->startTime = microtime(true);
        $request->attributes->set('ermo_module', $moduleSlug);
        $request->attributes->set('ermo_start_time', $this->startTime);

        if (config('ermo.emergency_bypass')) {
            return $next($request);
        }

        if ($this->isRedisDegraded()) {
            return $next($request);
        }

        $statuses = $this->moduleManager->getCachedStatuses();
        $module = $statuses[$moduleSlug] ?? null;
        $maxConcurrent = $module['max_concurrent'] ?? config('ermo.resilience.load_shedding.default_max_concurrent', 20);

        // Tag request with current module state from cache
        $request->attributes->set('ermo_module_state', $module['status'] ?? 'unknown');

        $current = $this->safeIncrementAndCheckLoad($moduleSlug, $maxConcurrent);

        if ($current === -1) {
            return $this->handleLoadShedding($moduleSlug, $request);
        }

        return $next($request);
    }

    /**
     * Terminate the request lifecycle.
     */
    public function terminate(Request $request, Response $response): void
    {
        $moduleSlug = $request->attributes->get('ermo_module');
        if (!$moduleSlug)
            return;

        $loadShed = $request->attributes->get('ermo_load_shed');

        // 1. Decrement active requests if not load shed
        if (!$loadShed && !config('ermo.emergency_bypass') && !$this->isRedisDegraded()) {
            $this->safeDecrementActiveRequests($moduleSlug);
        }

        // 2. Observability: Record Trace
        $latency = (int) ((microtime(true) - $request->attributes->get('ermo_start_time', microtime(true))) * 1000);
        $this->recordTrace($request, $response, $moduleSlug, $latency);
    }

    protected function recordTrace(Request $request, Response $response, string $slug, int $latency): void
    {
        try {
            $state = $request->attributes->get('ermo_module_state', 'unknown');
            $requestId = $request->attributes->get('request_id') ?? \Illuminate\Support\Str::uuid();

            // Store in DB for Mission Control (Could be async in production)
            ModuleRequestTrace::create([
                'request_id' => $requestId,
                'module_slug' => $slug,
                'module_state' => $state,
                'latency_ms' => $latency,
                'http_method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);

            // Update Module aggregated metrics
            Module::where('slug', $slug)->update([
                'total_requests' => DB::raw('total_requests + 1'),
                'total_latency_ms' => DB::raw('total_latency_ms + ' . (int) $latency),
            ]);

            // Real-time buffer in Redis (Last 50 traces per module for Livewire)
            $redisKey = "ermo:traces:{$slug}";
            $traceData = json_encode([
                'id' => (string) $requestId,
                'status' => $response->getStatusCode(),
                'latency' => $latency,
                'state' => $state,
                'time' => now()->toIso8601String()
            ]);

            Redis::lpush($redisKey, $traceData);
            Redis::ltrim($redisKey, 0, 49);
            Redis::expire($redisKey, 3600);

        } catch (\Exception $e) {
            Log::error("ERMO Tracing Failed: " . $e->getMessage());
        }
    }

    protected function safeIncrementAndCheckLoad(string $slug, int $max): int
    {
        try {
            $key = "ermo:active_requests:{$slug}";

            $script = "
                local current = tonumber(redis.call('GET', KEYS[1]) or '0')
                if current < tonumber(ARGV[2]) then
                    local v = redis.call('INCR', KEYS[1])
                    if tonumber(v) == 1 then
                        redis.call('EXPIRE', KEYS[1], ARGV[1])
                    end
                    return v
                else
                    return -1
                end
            ";

            return (int) $this->runLua($script, 1, $key, 600, $max);
        } catch (\Exception $e) {
            $this->triggerRedisDegraded($e);
            return 0;
        }
    }

    protected function safeDecrementActiveRequests(string $slug): void
    {
        try {
            $key = "ermo:active_requests:{$slug}";

            $script = "
                local current = tonumber(redis.call('GET', KEYS[1]) or '0')
                if current > 0 then
                    return redis.call('DECR', KEYS[1])
                end
                return 0
            ";

            $this->runLua($script, 1, $key);
        } catch (\Exception $e) {
        }
    }

    protected function handleLoadShedding(string $slug, Request $request): Response
    {
        Log::warning("ERMO: Load shedding triggered for {$slug}. Max concurrent requests reached.");
        $request->attributes->set('ermo_load_shed', true);

        $message = "النظام مثقل حالياً. الموديول {$slug} وصل للحد الأقصى من الطلبات المتزامنة.";

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['status' => 'error', 'code' => 'LOAD_SHEDDING', 'message' => $message], 503)->header('Retry-After', 30);
        }

        return response($message, 503)->header('Retry-After', 30);
    }

    protected function runLua(string $script, int $numKeys, ...$args)
    {
        $shaKey = md5($script);
        $sha = self::$shaCache[$shaKey] ?? null;

        try {
            if ($sha) {
                return Redis::evalSha($sha, $numKeys, ...$args);
            }
        } catch (\Exception $e) {
        }

        $result = Redis::eval($script, $numKeys, ...$args);
        self::$shaCache[$shaKey] = sha1($script);
        return $result;
    }

    protected function isRedisDegraded(): bool
    {
        return Cache::store('file')->has('ermo:redis_degraded');
    }

    protected function triggerRedisDegraded(\Exception $e): void
    {
        Log::emergency("ERMO: Redis fail-safe triggered. Error: {$e->getMessage()}");
        Cache::store('file')->put('ermo:redis_degraded', true, config('ermo.resilience.redis_failover_ttl', 30));
    }
}
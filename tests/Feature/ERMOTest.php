<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Core\Domain\Models\Module;
use Modules\Core\Application\Services\ModuleManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class ERMOTest extends TestCase
{
    use RefreshDatabase;

    protected ModuleManagerService $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orchestrator = app(ModuleManagerService::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_syncs_modules_from_filesystem()
    {
        $this->orchestrator->syncFromFilesystem();

        $this->assertDatabaseHas('modules', ['slug' => 'core']);
        $this->assertDatabaseHas('modules', ['slug' => 'users']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_transitions_states_atomically_with_versioning()
    {
        $module = Module::factory()->create([
            'slug' => 'test-mod',
            'status' => 'registered',
            'state_version' => 1
        ]);

        $this->orchestrator->transitionState('test-mod', 'installed');

        $module->refresh();
        $this->assertEquals('installed', $module->status);
        $this->assertEquals(2, $module->state_version);
        
        $cached = $this->orchestrator->getCachedStatuses();
        $this->assertEquals(2, $cached['test-mod']['version']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_illegal_state_transitions()
    {
        $module = Module::factory()->create([
            'slug' => 'illegal-mod',
            'status' => 'registered'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Illegal state transition');

        $this->orchestrator->transitionState('illegal-mod', 'active'); // Skip installed
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_enforces_core_protection()
    {
        $module = Module::factory()->create([
            'slug' => 'system-core',
            'status' => 'active',
            'is_core' => true
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Core module system-core cannot be disabled');

        $this->orchestrator->transitionState('system-core', 'disabled');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_detects_circular_dependencies()
    {
        $a = Module::factory()->create(['slug' => 'mod-a', 'name' => 'A']);
        $b = Module::factory()->create(['slug' => 'mod-b', 'name' => 'B']);

        $a->dependencies()->attach($b->id);
        $b->dependencies()->attach($a->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Circular dependency detected');

        $this->orchestrator->getSortedModules();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_enforces_circuit_breaker_ratio_trip()
    {
        $module = Module::factory()->create([
            'slug' => 'breaker-mod',
            'status' => 'active',
            'health_status' => 'healthy'
        ]);

        $healthService = app(\Modules\Core\Application\Services\HealthOrchestratorService::class);
        
        $checker = new class implements \Modules\Core\Domain\Interfaces\ModuleHealthContract {
            public function checks(): array {
                return ['status' => 'critical', 'impact_score' => 100, 'blocking' => true, 'details' => []];
            }
            public function critical(): bool { return true; }
        };

        // We need 5 failures (default threshold)
        for($i=1; $i<=5; $i++) {
            $healthService->checkModule($module, $checker);
        }

        $module->refresh();
        $this->assertEquals('degraded', $module->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_tags_requests_and_counts_active_metrics()
    {
        $module = Module::factory()->create(['slug' => 'tag-mod', 'status' => 'active']);
        $prefix = config('cache.prefix', 'laravel_cache');
        $key = "{$prefix}:ermo:active_requests:tag-mod";
        
        $middleware = new \Modules\Core\Http\Middleware\TraceModuleLifecycle();
        $request = new \Illuminate\Http\Request();
        
        $middleware->handle($request, function() use ($request, $key) {
            $this->assertEquals('tag-mod', $request->attributes->get('ermo_module'));
            $this->assertEquals(1, (int)\Illuminate\Support\Facades\Redis::get($key));
            return new \Illuminate\Http\Response();
        }, 'tag-mod');

        $middleware->terminate($request, new \Illuminate\Http\Response());
        $this->assertEquals(0, (int)\Illuminate\Support\Facades\Redis::get($key));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_respects_emergency_bypass_but_keeps_tracing()
    {
        config(['ermo.emergency_bypass' => true]);
        
        $module = Module::factory()->create(['slug' => 'bypass-mod', 'status' => 'disabled']);
        $middleware = app(\Modules\Core\Http\Middleware\CheckModuleStatus::class);
        $request = new \Illuminate\Http\Request();
        
        $response = $middleware->handle($request, function() {
            return new \Illuminate\Http\Response('bypassed');
        }, 'bypass-mod');

        $this->assertEquals('bypassed', $response->getContent());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_exports_prometheus_metrics()
    {
        Module::factory()->create(['slug' => 'prom-mod', 'status' => 'active', 'health_status' => 'healthy']);
        
        $response = $this->get('/api/v1/ermo/prometheus-metrics');
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('ermo_module_active_requests{module="prom-mod"}', $response->getContent());
        $this->assertStringContainsString('ermo_module_state{module="prom-mod"} 1', $response->getContent());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_survives_redis_downtime_with_isolation()
    {
        \Illuminate\Support\Facades\Redis::shouldReceive('evalSha')->andThrow(new \Exception('Redis Down'));
        \Illuminate\Support\Facades\Redis::shouldReceive('eval')->andThrow(new \Exception('Redis Down'));

        $middleware = new \Modules\Core\Http\Middleware\TraceModuleLifecycle();
        $request = new \Illuminate\Http\Request();
        
        $response = $middleware->handle($request, function() {
            return new \Illuminate\Http\Response('survived');
        }, 'chaos-mod');

        $this->assertEquals('survived', $response->getContent());
        $this->assertTrue(Cache::store('file')->has('ermo:redis_degraded'));
    }
}
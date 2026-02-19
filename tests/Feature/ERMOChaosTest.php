<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Core\Domain\Models\Module;
use Modules\Core\Application\Services\{HealthOrchestratorService, ModuleManagerService};
use Modules\Core\Domain\Interfaces\ModuleManagerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Cache, Redis, Log, Config};

class ERMOChaosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
        Config::set('ermo.emergency_bypass', false);
    }

    /**
     * Phase A: Concurrent Trip Guard Test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function phase_a_concurrent_trip_guard()
    {
        $module = Module::factory()->create(['slug' => 'concurrent-mod', 'status' => 'active']);
        
        $managerMock = \Mockery::mock(ModuleManagerService::class);
        $managerMock->shouldReceive('getCachedStatuses')->andReturn([
            'concurrent-mod' => ['status' => 'active', 'max_concurrent' => 100]
        ]);
        $managerMock->shouldReceive('transitionState')
            ->once()
            ->with('concurrent-mod', 'degraded', \Mockery::any());

        $healthService = new HealthOrchestratorService($managerMock);
        
        $checker = new class implements \Modules\Core\Domain\Interfaces\ModuleHealthContract {
            public function checks(): array {
                return ['status' => 'critical', 'impact_score' => 100, 'blocking' => true, 'details' => []];
            }
            public function critical(): bool { return true; }
        };

        for($i=1; $i<5; $i++) {
            $healthService->checkModule($module, $checker);
        }

        $healthService->checkModule($module, $checker);
        $this->assertTrue(true); 
    }

    /**
     * Phase B: Redis Cut during lifecycle
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function phase_b_redis_cut_isolation()
    {
        Cache::store('file')->forget('ermo:redis_degraded');

        Redis::shouldReceive('evalSha')->andThrow(new \Exception('Connection Lost'));
        Redis::shouldReceive('eval')->andThrow(new \Exception('Connection Lost'));

        $middleware = app(\Modules\Core\Http\Middleware\TraceModuleLifecycle::class);
        $request = new \Illuminate\Http\Request();
        
        $response = $middleware->handle($request, function() {
            return new \Illuminate\Http\Response('ok');
        }, 'redis-chaos');

        $this->assertEquals('ok', $response->getContent());
        $this->assertTrue(Cache::store('file')->has('ermo:redis_degraded'));
    }

    /**
     * Phase C: State Clash Management
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function phase_c_state_clash_resolution()
    {
        $module = Module::factory()->create([
            'slug' => 'clash-mod', 
            'status' => 'degraded',
            'health_status' => 'critical'
        ]);

        $manager = app(ModuleManagerInterface::class);
        $healthService = app(HealthOrchestratorService::class);

        $manager->transitionState('clash-mod', 'maintenance', 'Manual override');

        $checker = new class implements \Modules\Core\Domain\Interfaces\ModuleHealthContract {
            public function checks(): array {
                return ['status' => 'critical', 'impact_score' => 100, 'blocking' => true, 'details' => []];
            }
            public function critical(): bool { return true; }
        };

        $healthService->checkModule($module, $checker);
        $this->assertEquals('maintenance', $module->refresh()->status);
    }

    /**
     * Phase D: Load Shedding (Backpressure)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function phase_d_load_shedding_backpressure()
    {
        $slug = 'shed-mod';
        Module::factory()->create([
            'slug' => $slug, 
            'status' => 'active', 
            'max_concurrent_requests' => 1
        ]);
        
        $manager = app(ModuleManagerInterface::class);
        $manager->refreshCache();

        $middleware = app(\Modules\Core\Http\Middleware\TraceModuleLifecycle::class);
        $request = new \Illuminate\Http\Request();

        // Simulate Saturated State directly for handle call
        Redis::shouldReceive('evalSha')->andReturn(-1);
        Redis::shouldReceive('eval')->andReturn(-1);
        
        $response = $middleware->handle($request, function() { 
            return new \Illuminate\Http\Response('fail'); 
        }, $slug);

        $this->assertEquals(503, $response->getStatusCode());
        $this->assertStringContainsString('LOAD_SHEDDING', $response->getContent());
        $this->assertTrue($request->attributes->get('ermo_load_shed'));
    }
}
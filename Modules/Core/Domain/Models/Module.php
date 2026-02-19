<?php

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Module extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\ModuleFactory::new ();
    }
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'slug',
        'name',
        'version',
        'status',
        'is_core',
        'priority',
        'feature_flags',
        'metadata',
        'health_status',
        'max_concurrent_requests',
        'state_version',
        'total_requests',
        'total_latency_ms',
        'uptime_seconds',
        'last_status_change_at',
        'degradation_count',
        'sla_target',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'feature_flags' => 'array',
        'metadata' => 'array',
        'state_version' => 'integer',
        'priority' => 'integer',
        'max_concurrent_requests' => 'integer',
        'total_requests' => 'integer',
        'total_latency_ms' => 'integer',
        'uptime_seconds' => 'integer',
        'degradation_count' => 'integer',
        'sla_target' => 'decimal:2',
        'last_status_change_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string)Str::uuid();
            }
        });
    }

    /**
     * Define the allowed state transitions.
     */
    public const ALLOWED_TRANSITIONS = [
        'registered' => ['installed', 'disabled'],
        'installed' => ['active', 'maintenance', 'disabled'],
        'active' => ['degraded', 'maintenance', 'disabled'],
        'degraded' => ['active', 'maintenance', 'disabled'],
        'maintenance' => ['active', 'degraded', 'disabled'],
        'disabled' => ['active', 'maintenance'],
    ];

    /**
     * Check if a transition to a new status is allowed.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        if ($this->status === $newStatus) {
            return true;
        }

        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? []);
    }

    /**
     * Update status with atomic versioning and uptime tracking.
     */
    public function transitionTo(string $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \Exception("Illegal state transition from {$this->status} to {$newStatus}");
        }

        $now = now();
        $secondsSinceLastChange = $this->last_status_change_at ? $now->diffInSeconds($this->last_status_change_at) : 0;

        // Track uptime if we were active or degraded
        if (in_array($this->status, ['active', 'degraded'])) {
            $this->uptime_seconds += $secondsSinceLastChange;
        }

        if ($newStatus === 'degraded' && $this->status !== 'degraded') {
            $this->degradation_count++;
        }

        $this->status = $newStatus;
        $this->state_version++;
        $this->last_status_change_at = $now;

        return $this->save();
    }

    public function getUptimePercentage(): float
    {
        $totalSeconds = now()->diffInSeconds($this->created_at);
        if ($totalSeconds <= 0) return 100.0;

        $currentUptime = $this->uptime_seconds;
        if (in_array($this->status, ['active', 'degraded'])) {
            $currentUptime += now()->diffInSeconds($this->last_status_change_at);
        }

        return min(100.0, ($currentUptime / $totalSeconds) * 100);
    }

    public function getAvgLatency(): float
    {
        return $this->total_requests > 0 ? ($this->total_latency_ms / $this->total_requests) : 0;
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Module::class , 'module_dependencies', 'module_id', 'depends_on_id');
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(Module::class , 'module_dependencies', 'depends_on_id', 'module_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    public function hasFeature(string $feature): bool
    {
        return $this->feature_flags[$feature] ?? false;
    }
}
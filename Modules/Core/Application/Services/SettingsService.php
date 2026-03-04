<?php

namespace Modules\Core\Application\Services;

use Modules\Core\Domain\Interfaces\SettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class SettingsService extends BaseService
{
    protected SettingRepositoryInterface $repository;
    protected ?Collection $settings = null;
    protected string $cacheKey = 'system_settings_cache';

    public function __construct(SettingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Load all settings and cache them.
     */
    public function loadSettings(): Collection
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $this->settings = Cache::get($this->cacheKey);

        if ($this->settings === null) {
            $this->settings = $this->repository->getAllSettings()->pluck('value', 'name');
            Cache::put($this->cacheKey, $this->settings, now()->addDays(1));
        }

        return $this->settings;
    }

    /**
     * Get a specific setting by name.
     */
    public function get(string $name, mixed $default = null): mixed
    {
        try { $settings = $this->loadSettings(); } catch (\Exception $e) { $settings = collect(); }
        return $settings->get($name, $default);
    }

    /**
     * Clear settings cache.
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
        $this->settings = null;
    }

    /**
     * Update or create a setting.
     */
    public function set(string $name, mixed $value, string $module = 'Core', bool $encrypted = false): void
    {
        $this->repository->updateOrCreate(
            ['name' => $name],
            [
                'value' => $value,
                'module' => $module,
                'is_encrypted' => $encrypted
            ]
        );

        $this->clearCache();
    }
}
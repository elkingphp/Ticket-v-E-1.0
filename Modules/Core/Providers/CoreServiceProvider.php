<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CoreServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Core';

    protected string $nameLower = 'core';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'Infrastructure/Database/Migrations'));

        // Wildcard listener for module deletion to cleanup permissions
        \Illuminate\Support\Facades\Event::listen('modules.*.deleted', function ($eventName, array $data) {
            $module = $data[0];
            if ($module instanceof \Nwidart\Modules\Module) {
                app(\Modules\Core\Application\Services\ModulePermissionService::class)
                    ->deletePermissionsByModule($module->getName());
            }
        });

        // Global View Composer for System Settings
        View::composer('*', \Modules\Core\Http\View\Composers\SystemSettingsComposer::class);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Repositories
        $this->app->bind(
            \Modules\Core\Domain\Interfaces\SettingRepositoryInterface::class,
            \Modules\Core\Infrastructure\Repositories\SettingRepository::class
        );

        // Core Services
        $this->app->singleton(\Modules\Core\Application\Services\SettingsService::class);
        $this->app->singleton(\Modules\Core\Application\Services\ModulePermissionService::class);
        $this->app->singleton(
            \Modules\Core\Domain\Interfaces\ModuleManagerInterface::class,
            \Modules\Core\Application\Services\ModuleManagerService::class
        );
        $this->app->singleton(\Modules\Core\Application\Services\HealthOrchestratorService::class);
        $this->app->singleton(
            \Modules\Core\Domain\Interfaces\BackupServiceInterface::class,
            \Modules\Core\Application\Services\BackupService::class
        );
        $this->app->singleton(\Modules\Core\Application\Services\BackupService::class);
        $this->app->singleton(\Modules\Core\Application\Services\AuditLoggerService::class);


        // Dashboard Services
        $this->app->singleton(\Modules\Core\Application\Services\Dashboard\Metrics\UserMetricsService::class);
        $this->app->singleton(\Modules\Core\Application\Services\Dashboard\Metrics\AuditMetricsService::class);
        $this->app->singleton(\Modules\Core\Application\Services\Dashboard\Metrics\SystemHealthService::class);
        $this->app->singleton(\Modules\Core\Application\Services\Dashboard\DashboardOrchestrator::class);

        // Notification Services
        $this->app->singleton(\Modules\Core\Application\Services\FallbackLogger::class);
        $this->app->singleton(\Modules\Core\Application\Services\AlertService::class);
        $this->app->singleton(\Modules\Core\Application\Services\NotificationMonitor::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\Core\Infrastructure\Console\AuditCleanupCommand::class,
            \Modules\Core\Infrastructure\Console\BackupListCommand::class,
            \Modules\Core\Infrastructure\Console\BackupRestoreCommand::class,
            \Modules\Core\Infrastructure\Console\SyncModulesCommand::class,
            \Modules\Core\Infrastructure\Console\ChaosSimulationCommand::class,
            \Modules\Core\Infrastructure\Console\MigrateLegacyData::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $schedule->command('audit:cleanup')->daily();
        });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'Resources/lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'Resources/lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower . '.' . $config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->nameLower);
        $sourcePath = module_path($this->name, 'Resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace') . '\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->nameLower)) {
                $paths[] = $path . '/modules/' . $this->nameLower;
            }
        }

        return $paths;
    }
}
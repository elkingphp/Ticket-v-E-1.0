<?php

namespace Modules\Settings\Application\Services;

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\File;

class SettingsDiscoveryService
{
    /**
     * Get all settings definitions from active modules.
     * 
     * @return array
     */
    public function getActiveSettingsDefinitions(): array
    {
        $definitions = [];
        $enabledModules = Module::allEnabled();

        foreach ($enabledModules as $module) {
            $configPath = $module->getPath() . '/Config/settings.php';

            // Try lowercase config too if that fails
            if (!File::exists($configPath)) {
                $configPath = $module->getPath() . '/config/settings.php';
            }

            if (File::exists($configPath)) {
                $moduleSettings = require $configPath;
                if (is_array($moduleSettings)) {
                    foreach ($moduleSettings as $group => $data) {
                        if (!isset($definitions[$group])) {
                            $definitions[$group] = [
                                'label' => 'settings::settings.' . $group,
                                'description' => '',
                                'icon' => 'list-settings-line',
                                'settings' => []
                            ];
                        }

                        // Check if it's the new nested structure
                        if (isset($data['settings']) && is_array($data['settings'])) {
                            // Merge metadata if present
                            if (isset($data['label'])) {
                                $definitions[$group]['label'] = $data['label'];
                            }
                            if (isset($data['description'])) {
                                $definitions[$group]['description'] = $data['description'];
                            }
                            if (isset($data['icon'])) {
                                $definitions[$group]['icon'] = $data['icon'];
                            }

                            // Merge settings
                            $definitions[$group]['settings'] = array_merge($definitions[$group]['settings'], $data['settings']);
                        } else {
                            // Legacy flat structure
                            $definitions[$group]['settings'] = array_merge($definitions[$group]['settings'], $data);
                        }
                    }
                }
            }
        }

        return $definitions;
    }
}

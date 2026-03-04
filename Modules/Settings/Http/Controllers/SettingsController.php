<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Domain\Interfaces\SettingRepositoryInterface;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingsController extends Controller implements HasMiddleware
{
    protected $settingRepository;
    protected $discoveryService;

    public function __construct(
        SettingRepositoryInterface $settingRepository,
        \Modules\Settings\Application\Services\SettingsDiscoveryService $discoveryService
    ) {
        $this->settingRepository = $settingRepository;
        $this->discoveryService = $discoveryService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['index']),
            new Middleware('permission:settings.manage', only: ['update']),
        ];
    }

    public function index()
    {
        $definitions = $this->discoveryService->getActiveSettingsDefinitions();
        $allSettings = $this->settingRepository->all()->keyBy('key');

        $settings = [];
        $groupMetadata = [];

        foreach ($definitions as $group => $groupData) {
            $groupMetadata[$group] = [
                'label' => $groupData['label'] ?? 'settings::settings.' . $group,
                'description' => $groupData['description'] ?? '',
                'icon' => $groupData['icon'] ?? 'list-settings-line',
            ];

            $groupSettingsList = [];
            foreach ($groupData['settings'] as $key => $definition) {
                $options = [];
                if (isset($definition['options'])) {
                    $options = $definition['options'];
                } elseif (isset($definition['options_source'])) {
                    $options = $this->loadOptions($definition['options_source']);
                }

                if ($allSettings->has($key)) {
                    $setting = $allSettings->get($key);
                    $setting->label = $definition['label'] ?? 'settings::settings.' . $key;
                    $setting->description = $definition['description'] ?? '';
                    $setting->type = $definition['type'] ?? $setting->type;
                    $setting->options = $options;
                    $groupSettingsList[] = $setting;
                } else {
                    $groupSettingsList[] = (object) [
                        'key' => $key,
                        'value' => $definition['default'] ?? '',
                        'type' => $definition['type'] ?? 'string',
                        'group' => $group,
                        'label' => $definition['label'] ?? 'settings::settings.' . $key,
                        'description' => $definition['description'] ?? '',
                        'options' => $options,
                        'id' => 9999 + count($groupSettingsList), // Default high ID
                        'sort_order' => 9999
                    ];
                }
            }

            // Sort by sort_order then id
            usort($groupSettingsList, function ($a, $b) {
                $aOrder = $a->sort_order ?? 9999;
                $bOrder = $b->sort_order ?? 9999;

                if ($aOrder === $bOrder) {
                    $aId = $a->id ?? 9999;
                    $bId = $b->id ?? 9999;
                    return $aId <=> $bId;
                }

                return $aOrder <=> $bOrder;
            });

            $settings[$group] = $groupSettingsList;
        }

        return view('settings::index', compact('settings', 'groupMetadata'));
    }

    protected function loadOptions(string $source): array
    {
        return match ($source) {
            'roles' => \Spatie\Permission\Models\Role::all()->mapWithKeys(fn($role) => [$role->name => $role->display_name ?? $role->name])->toArray(),
            'programs' => \Modules\Educational\Domain\Models\Program::active()->orderBy('name')->pluck('name', 'id')->toArray(),
            'tracks' => \Modules\Educational\Domain\Models\Track::active()->orderBy('name')->pluck('name', 'id')->toArray(),
            default => [],
        };
    }

    public function update(Request $request)
    {
        $definitions = $this->discoveryService->getActiveSettingsDefinitions();
        $flattenedDefinitions = [];
        foreach ($definitions as $group => $groupData) {
            foreach ($groupData['settings'] as $key => $def) {
                $flattenedDefinitions[$key] = array_merge($def, ['group' => $group]);
            }
        }

        // Handle regular inputs and booleans
        foreach ($flattenedDefinitions as $key => $def) {
            if ($def['type'] === 'image') {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    if ($file->isValid()) {
                        $path = $file->store('settings', 'public');
                        $value = 'storage/' . $path;

                        \Modules\Settings\Domain\Models\Setting::updateOrCreate(
                            ['key' => $key],
                            [
                                'value' => $value,
                                'group' => $def['group'],
                                'type' => 'image',
                                'label' => $def['label'] ?? $key
                            ]
                        );
                        \Illuminate\Support\Facades\Cache::forget("setting_{$key}");
                    }
                }
                continue;
            }

            // Handle Booleans and Multiselects
            if ($def['type'] === 'boolean') {
                $value = $request->has($key) ? '1' : '0';
            } elseif ($def['type'] === 'multiselect') {
                $value = $request->input($key, []);
            } else {
                if (!$request->has($key))
                    continue;
                $value = $request->input($key);
            }

            \Modules\Settings\Domain\Models\Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'group' => $def['group'],
                    'type' => $def['type'] ?? 'string',
                    'label' => $def['label'] ?? $key
                ]
            );

            \Illuminate\Support\Facades\Cache::forget("setting_{$key}");
        }

        \Illuminate\Support\Facades\Cache::forget("settings_all");

        return back()->with([
            'success' => __('settings::settings.settings_updated'),
            'active_tab' => $request->active_tab
        ]);
    }
}
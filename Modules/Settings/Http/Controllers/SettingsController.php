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

    public function __construct(SettingRepositoryInterface $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view settings', only: ['index']),
            new Middleware('permission:manage settings', only: ['update']),
        ];
    }

    public function index()
    {
        $settings = $this->settingRepository->all()->groupBy('group');
        return view('settings::index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        // Handle regular inputs
        foreach ($data as $key => $value) {
            // Only update if it's not a file (files handled below)
            if (!$request->hasFile($key)) {
                $this->settingRepository->setByKey($key, $value);
            }
        }

        // Handle file uploads
        foreach ($request->allFiles() as $key => $file) {
            if ($file->isValid()) {
                $path = $file->store('settings', 'public');
                $this->settingRepository->setByKey($key, 'storage/' . $path);
            }
        }

        return back()->with('success', __('settings::settings.settings_updated'));
    }

}
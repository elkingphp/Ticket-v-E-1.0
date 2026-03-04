<?php

namespace Modules\Core\Http\View\Composers;

use Illuminate\View\View;
use Modules\Core\Application\Services\SettingsService;
use Modules\Settings\Domain\Models\Setting;

class SystemSettingsComposer
{
    protected SettingsService $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    public function compose(View $view)
    {
        // Load all public settings into the view
        $allSettings = Setting::where('is_public', true)->get()->pluck('value', 'key');

        $view->with('systemSettings', $allSettings);
    }
}

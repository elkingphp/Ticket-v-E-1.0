<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Educational\Application\Services\EvaluationSettings;

class EvaluationSettingsController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected EvaluationSettings $settings
    ) {
    }

    /**
     * Display the evaluation settings page.
     */
    public function index()
    {
        $this->authorize('manage', \Modules\Educational\Domain\Models\EvaluationForm::class);

        $types = \Modules\Educational\Domain\Models\EvaluationType::withCount('forms')->get();
        $targetTypes = \Modules\Educational\Domain\Models\EvaluationType::TARGET_TYPES;
        $roles = \Spatie\Permission\Models\Role::all()->mapWithKeys(fn($role) => [$role->name => $role->display_name ?? $role->name])->toArray();

        return view('modules.educational.evaluations.settings', compact('types', 'targetTypes', 'roles'));
    }

    /**
     * Update evaluation settings.
     */
    public function update(Request $request)
    {
        $this->authorize('manage', \Modules\Educational\Domain\Models\EvaluationForm::class);

        $validated = $request->validate([
            // --- Analytics Group ---
            'red_flag_threshold' => 'required|numeric|min:1.0|max:5.0',
            'red_flag_enabled' => 'required|boolean',
            'results_cache_seconds' => 'required|integer|min:0|max:86400',

            // --- Notifications Group ---
            'notification_channel' => 'required|in:mail,database,mail_and_database',
            'assignment_notify_enabled' => 'required|boolean',

            // --- Scheduler Group ---
            'weekly_report_day' => 'required|in:mondays,tuesdays,wednesdays,thursdays,fridays,saturdays,sundays',
            'weekly_report_time' => 'required|regex:/^\d{2}:\d{2}$/',
        ]);

        $this->settings->updateSettings($validated);

        return redirect()
            ->route('educational.evaluations.settings.index')
            ->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}

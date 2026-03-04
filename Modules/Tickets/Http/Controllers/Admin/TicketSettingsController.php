<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use Modules\Core\Domain\Models\SystemSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketSettingsController extends Controller
{
    protected $moduleNamespce = 'tickets';

    public function index()
    {
        $settings = SystemSetting::where('module', $this->moduleNamespce)->get()->pluck('value', 'name');

        // Defaults
        $defaults = [
            'auto_close_after_days' => 7,
            'allow_reopen' => 1,
            'max_tickets_per_user_per_day' => 5,
            'ticket_number_format' => 'TICK-{ID}',
            'ticket_admin_role' => 'admin',
        ];

        $settings = array_merge($defaults, $settings->toArray());
        $roles = \Modules\Users\Domain\Models\Role::all();

        return view('tickets::admin.settings.index', compact('settings', 'roles'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'auto_close_after_days' => 'required|integer|min:0',
            'allow_reopen' => 'required|boolean',
            'max_tickets_per_user_per_day' => 'required|integer|min:1',
            'ticket_number_format' => 'required|string|max:100',
            'ticket_admin_role' => 'required|string|max:100',
        ]);

        foreach ($data as $name => $value) {
            SystemSetting::updateOrCreate(
                ['module' => $this->moduleNamespce, 'name' => $name],
                ['value' => $value]
            );
        }

        return redirect()->route('admin.tickets.settings')
            ->with('success', __('tickets::messages.messages.updated'));
    }
}

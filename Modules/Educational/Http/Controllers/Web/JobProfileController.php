<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\JobProfile;
use Modules\Educational\Domain\Models\Track;
use Modules\Core\Domain\Models\SystemSetting;
use Modules\Users\Domain\Models\User;
use Spatie\Permission\Models\Role;

class JobProfileController extends Controller
{
    protected function getFilteredUsers()
    {
        $setting = SystemSetting::where('module', 'Educational')
            ->where('name', 'job_profile_responsible_roles')
            ->first();

        $roleIds = [];
        if ($setting && !empty($setting->value)) {
            $roleIds = is_string($setting->value) ? json_decode($setting->value, true) : $setting->value;
        }

        if (!empty($roleIds)) {
            return User::whereHas('roles', function ($q) use ($roleIds) {
                $q->whereIn('id', $roleIds);
            })->get();
        }

        return User::all();
    }

    public function index(Request $request)
    {
        $query = JobProfile::with(['track', 'responsibles']);

        if ($request->filled('track_id')) {
            $query->where('track_id', $request->track_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                    ->orWhere('code', 'ilike', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) (SystemSetting::where('module', 'Educational')
            ->where('name', 'job_profile_per_page')
            ->first()->value ?? 12);

        $profiles = $query->latest()->paginate($perPage)->withQueryString();
        $tracks = Track::active()->get();

        return view('educational::job_profiles.index', compact('profiles', 'tracks'));
    }

    public function create()
    {
        $tracks = Track::active()->get();
        $users = $this->getFilteredUsers();
        return view('educational::job_profiles.create', compact('tracks', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:pgsql.education.job_profiles,code',
            'track_id' => 'required|exists:pgsql.education.tracks,id',
            'status' => 'required|in:active,inactive',
            'responsibles' => 'nullable|array',
            'responsibles.*' => 'exists:users,id'
        ]);

        $profile = JobProfile::create($validated);

        if ($request->has('responsibles')) {
            $profile->responsibles()->sync($request->responsibles);
        }

        return redirect()->route('educational.job_profiles.index')->with('success', __('educational::messages.job_profile_created'));
    }

    public function edit($id)
    {
        $profile = JobProfile::with('responsibles')->findOrFail($id);
        $tracks = Track::active()->get();
        $users = $this->getFilteredUsers();
        return view('educational::job_profiles.edit', compact('profile', 'tracks', 'users'));
    }

    public function update(Request $request, $id)
    {
        $profile = JobProfile::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:pgsql.education.job_profiles,code,' . $profile->id,
            'track_id' => 'required|exists:pgsql.education.tracks,id',
            'status' => 'required|in:active,inactive',
            'responsibles' => 'nullable|array',
            'responsibles.*' => 'exists:users,id'
        ]);

        $profile->update($validated);

        if ($request->has('responsibles')) {
            $profile->responsibles()->sync($request->responsibles);
        } else {
            $profile->responsibles()->sync([]);
        }

        return redirect()->route('educational.job_profiles.index')->with('success', __('educational::messages.job_profile_updated'));
    }

    public function destroy($id)
    {
        $profile = JobProfile::findOrFail($id);
        $profile->delete();

        return redirect()->route('educational.job_profiles.index')->with('success', __('educational::messages.job_profile_deleted'));
    }

    public function settings()
    {
        $roles = Role::all();
        $roleSetting = SystemSetting::where('module', 'Educational')
            ->where('name', 'job_profile_responsible_roles')
            ->first();

        $selectedRoles = [];
        if ($roleSetting && !empty($roleSetting->value)) {
            $selectedRoles = is_string($roleSetting->value) ? json_decode($roleSetting->value, true) : $roleSetting->value;
        }

        $perPage = SystemSetting::where('module', 'Educational')
            ->where('name', 'job_profile_per_page')
            ->first()->value ?? 12;

        return view('modules.educational.job_profiles.settings', compact('roles', 'selectedRoles', 'perPage'));
    }

    public function saveSettings(Request $request)
    {
        $roles = $request->input('roles', []);
        $perPage = $request->input('per_page', 12);

        SystemSetting::updateOrCreate(
            ['module' => 'Educational', 'name' => 'job_profile_responsible_roles'],
            ['value' => json_encode($roles)]
        );

        SystemSetting::updateOrCreate(
            ['module' => 'Educational', 'name' => 'job_profile_per_page'],
            ['value' => $perPage]
        );

        return redirect()->route('educational.job_profiles.index')->with('success', __('educational::messages.settings_saved') ?? 'تم حفظ الإعدادات بنجاح');
    }
}

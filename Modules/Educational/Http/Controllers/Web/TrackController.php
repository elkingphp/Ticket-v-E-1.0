<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Track;
use Modules\Core\Domain\Models\SystemSetting;
use Spatie\Permission\Models\Role;
use Modules\Users\Domain\Models\User;
use Modules\Educational\Application\Services\EducationalSettings;

class TrackController extends Controller
{
    public function __construct(protected EducationalSettings $settings)
    {
    }

    public function index(Request $request)
    {
        $query = Track::with(['responsibles'])->withCount('jobProfiles');

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%')
                ->orWhere('code', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $tracks = $query->latest()->get();
        return view('educational::tracks.index', compact('tracks'));
    }

    protected function getFilteredUsers()
    {
        $roleNames = $this->settings->trackResponsibleRoles();

        if (!empty($roleNames)) {
            return User::role($roleNames)->get();
        }

        return User::all();
    }

    public function create()
    {
        $users = $this->getFilteredUsers();
        return view('educational::tracks.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:pgsql.education.tracks,code',
            'responsibles' => 'nullable|array',
            'responsibles.*' => 'exists:users,id'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $track = Track::create($validated);

        if ($request->has('responsibles')) {
            $track->responsibles()->sync($request->responsibles);
        }

        return redirect()->route('educational.tracks.index')->with('success', __('educational::messages.track_created'));
    }

    public function edit($id)
    {
        $track = Track::with('responsibles')->findOrFail($id);
        $users = $this->getFilteredUsers();
        return view('educational::tracks.edit', compact('track', 'users'));
    }

    public function update(Request $request, $id)
    {
        $track = Track::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:pgsql.education.tracks,code,' . $track->id,
            'responsibles' => 'nullable|array',
            'responsibles.*' => 'exists:users,id'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $track->update($validated);

        if ($request->has('responsibles')) {
            $track->responsibles()->sync($request->responsibles);
        } else {
            $track->responsibles()->sync([]);
        }

        return redirect()->route('educational.tracks.index')->with('success', __('educational::messages.track_updated'));
    }

    public function destroy($id)
    {
        $track = Track::findOrFail($id);

        if ($track->jobProfiles()->count() > 0) {
            return redirect()->back()->with('error', __('educational::messages.cannot_delete_track_with_profiles'));
        }

        $track->delete();

        return redirect()->route('educational.tracks.index')->with('success', __('educational::messages.track_deleted'));
    }
}

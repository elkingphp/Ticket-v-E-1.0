<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Governorate;

class GovernorateController extends Controller
{
    public function index()
    {
        $governorates = Governorate::latest()->get();
        return view('educational::governorates.index', compact('governorates'));
    }

    public function create()
    {
        return view('educational::governorates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        Governorate::create($validated);

        return redirect()->route('educational.governorates.index')->with('success', __('educational::messages.governorate_created'));
    }

    public function edit($id)
    {
        $governorate = Governorate::findOrFail($id);
        return view('educational::governorates.edit', compact('governorate'));
    }

    public function update(Request $request, $id)
    {
        $governorate = Governorate::findOrFail($id);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        $governorate->update($validated);

        return redirect()->route('educational.governorates.index')->with('success', __('educational::messages.governorate_updated'));
    }

    public function destroy($id)
    {
        $governorate = Governorate::findOrFail($id);

        // Check if governorate is used by any instructor
        $exists = \Modules\Educational\Domain\Models\InstructorProfile::where('governorate_id', $id)->exists();

        if ($exists) {
            return redirect()->back()->with('error', __('educational::messages.cannot_delete_governorate_in_use'));
        }

        $governorate->delete();

        return redirect()->route('educational.governorates.index')->with('success', __('educational::messages.governorate_deleted'));
    }
}

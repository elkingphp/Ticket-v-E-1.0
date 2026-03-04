<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\SessionType;

class SessionTypeController extends Controller
{
    public function index()
    {
        $types = SessionType::latest()->get();
        return view('educational::session_types.index', compact('types'));
    }

    public function create()
    {
        return view('educational::session_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        SessionType::create($validated);

        return redirect()->route('educational.session_types.index')->with('success', __('educational::messages.session_type_created'));
    }

    public function edit($id)
    {
        $type = SessionType::findOrFail($id);
        return view('educational::session_types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = SessionType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $type->update($validated);

        return redirect()->route('educational.session_types.index')->with('success', __('educational::messages.session_type_updated'));
    }

    public function destroy($id)
    {
        $type = SessionType::findOrFail($id);

        // Check for dependencies (lectures/templates) if needed

        $type->delete();

        return redirect()->route('educational.session_types.index')->with('success', __('educational::messages.session_type_deleted'));
    }
}

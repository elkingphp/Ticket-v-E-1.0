<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Modules\Educational\Domain\Models\EvaluationType;
use Modules\Educational\Domain\Models\EvaluationForm;

class EvaluationTypeController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('manage', EvaluationForm::class);
        $types = EvaluationType::withCount('forms')->get();
        $targetTypes = EvaluationType::TARGET_TYPES;
        $roles = \Spatie\Permission\Models\Role::pluck('name', 'id')->toArray();

        return view('educational::evaluations.settings', compact('types', 'targetTypes', 'roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage', EvaluationForm::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_type' => 'required|string|in:' . implode(',', array_keys(EvaluationType::TARGET_TYPES)),
            'allowed_roles' => 'required|array',
            'allowed_roles.*' => 'string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Ensure uniqueness for slug
        if (EvaluationType::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $validated['slug'] . '-' . uniqid();
        }

        EvaluationType::create($validated);

        return redirect(route('educational.evaluations.settings.index') . '#evaluation-types')
            ->with('success', 'تم إضافة نوع التقييم بنجاح.');
    }

    public function update(Request $request, EvaluationType $type)
    {
        $this->authorize('manage', EvaluationForm::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_type' => 'required|string|in:' . implode(',', array_keys(EvaluationType::TARGET_TYPES)),
            'allowed_roles' => 'required|array',
            'allowed_roles.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $type->update($validated);

        return redirect(route('educational.evaluations.settings.index') . '#evaluation-types')
            ->with('success', 'تم تحديث البيانات بنجاح.');
    }

    public function destroy(EvaluationType $type)
    {
        $this->authorize('manage', EvaluationForm::class);

        if ($type->forms()->count() > 0) {
            return redirect(route('educational.evaluations.settings.index') . '#evaluation-types')
                ->with('error', 'لا يمكن حذف النوع لوجود نماذج مرتبطة به.');
        }

        $type->delete();

        return redirect(route('educational.evaluations.settings.index') . '#evaluation-types')
            ->with('success', 'تم حذف النوع بنجاح.');
    }
}

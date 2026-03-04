<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Core\Application\Services\ApprovalService;
use Modules\Educational\Domain\Models\RoomType;

class RoomTypeController extends Controller
{
    public function index()
    {
        $types = RoomType::orderBy('sort_order')->latest('id')->get();
        return view('educational::room_types.index', compact('types'));
    }

    public function create()
    {
        return view('educational::room_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique(RoomType::class, 'slug')],
            'color' => 'required|in:primary,success,info,warning,danger,secondary,dark',
            'icon' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        RoomType::create($validated);

        return redirect()->route('educational.room_types.index')
            ->with('success', __('educational::messages.room_type_created'));
    }

    public function edit($id)
    {
        $type = RoomType::findOrFail($id);
        return view('educational::room_types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = RoomType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique(RoomType::class, 'slug')->ignore($type->id)],
            'color' => 'required|in:primary,success,info,warning,danger,secondary,dark',
            'icon' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $type->update($validated);

        return redirect()->route('educational.room_types.index')
            ->with('success', __('educational::messages.room_type_updated'));
    }

    public function destroy($id, ApprovalService $approvalService)
    {
        $type = RoomType::findOrFail($id);

        // منع الحذف إذا هناك طلب معلق بالفعل
        if ($type->pendingApprovalRequest()) {
            return redirect()->back()
                ->with('error', 'يوجد طلب معلق لحذف هذا النوع بالفعل.');
        }

        // منع الحذف إذا هناك قاعات مرتبطة
        if ($type->rooms()->count() > 0) {
            return redirect()->back()
                ->with('error', __('educational::messages.room_type_has_rooms'));
        }

        try {
            $approvalService->requestApproval(
                approvable: $type,
                schema: 'education',
                action: 'delete',
                metadata: [
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'reason' => 'حذف نوع القاعة',
                ],
                levels: 1
            );

            return redirect()->route('educational.room_types.index')
                ->with('success', 'تم إرسال طلب الحذف للمراجعة والموافقة.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'فشل في إرسال طلب الحذف: ' . $e->getMessage());
        }
    }
}

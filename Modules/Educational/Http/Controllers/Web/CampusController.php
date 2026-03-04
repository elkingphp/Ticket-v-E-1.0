<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Campus;
use Modules\Core\Application\Services\ApprovalService;

class CampusController extends Controller
{
    public function index(Request $request)
    {
        $query = Campus::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%')
                    ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        $campuses = $query->get();
        return view('educational::campuses.index', compact('campuses'));
    }

    public function create()
    {
        return view('educational::campuses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:' . Campus::class . ',code',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive'
        ]);

        Campus::create($validated);

        return redirect()->route('educational.campuses.index')->with('success', __('educational::messages.campus_created'));
    }

    public function edit($id)
    {
        $campus = Campus::findOrFail($id);
        return view('educational::campuses.edit', compact('campus'));
    }

    public function update(Request $request, $id)
    {
        $campus = Campus::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:' . Campus::class . ',code,' . $campus->id,
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive'
        ]);

        $campus->update($validated);

        return redirect()->route('educational.campuses.index')->with('success', __('educational::messages.campus_updated'));
    }

    public function destroy($id, ApprovalService $approvalService)
    {
        $campus = Campus::findOrFail($id);

        if ($campus->pendingApprovalRequest()) {
            return redirect()->back()->with('error', 'يوجد طلب معلق لحذف هذا المقر بالفعل.');
        }

        try {
            $approvalService->requestApproval(
                approvable: $campus,
                schema: 'education',
                action: 'delete',
                metadata: [
                    'name' => $campus->name ?? 'مقر بدون اسم',
                    'code' => $campus->code ?? 'بدون كود',
                    'reason' => 'حذف المقر / الفرع الرئيسي'
                ],
                levels: 1
            );

            return redirect()->route('educational.campuses.index')->with('success', 'تم إرسال طلب الحذف للمراجعة والموافقة.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'فشل في إرسال طلب الحذف: ' . $e->getMessage());
        }
    }
}

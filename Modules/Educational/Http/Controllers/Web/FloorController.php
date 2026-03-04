<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Floor;
use Modules\Educational\Domain\Models\Building;
use Modules\Core\Application\Services\ApprovalService;

class FloorController extends Controller
{
    public function index(Request $request)
    {
        $query = Floor::with('building.campus')->latest();

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $floors = $query->get();
        $buildings = Building::where('status', 'active')->get();

        return view('educational::floors.index', compact('floors', 'buildings'));
    }

    public function create()
    {
        $buildings = Building::where('status', 'active')->get();
        return view('educational::floors.create', compact('buildings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:' . Building::class . ',id',
            'floor_number' => 'required|string',
            'name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        Floor::create($validated);

        return redirect()->route('educational.floors.index')->with('success', 'Floor created successfully.');
    }

    public function edit($id)
    {
        $floor = Floor::findOrFail($id);
        $buildings = Building::where('status', 'active')->get();
        return view('educational::floors.edit', compact('floor', 'buildings'));
    }

    public function update(Request $request, $id)
    {
        $floor = Floor::findOrFail($id);

        $validated = $request->validate([
            'building_id' => 'required|exists:' . Building::class . ',id',
            'floor_number' => 'required|string',
            'name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        $floor->update($validated);

        return redirect()->route('educational.floors.index')->with('success', 'Floor updated successfully.');
    }

    public function destroy($id, ApprovalService $approvalService)
    {
        $floor = Floor::findOrFail($id);

        if ($floor->pendingApprovalRequest()) {
            return redirect()->back()->with('error', 'يوجد طلب معلق لحذف هذا الدور بالفعل.');
        }

        try {
            $approvalService->requestApproval(
                approvable: $floor,
                schema: 'education',
                action: 'delete',
                metadata: [
                    'name' => $floor->name ?? 'دور بدون اسم',
                    'floor_number' => $floor->floor_number ?? 'بدون رقم',
                    'building_name' => $floor->building->name ?? 'غير محدد',
                    'reason' => 'حذف الدور'
                ],
                levels: 1
            );

            return redirect()->route('educational.floors.index')->with('success', 'تم إرسال طلب الحذف للمراجعة والموافقة.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'فشل في إرسال طلب الحذف: ' . $e->getMessage());
        }
    }
}

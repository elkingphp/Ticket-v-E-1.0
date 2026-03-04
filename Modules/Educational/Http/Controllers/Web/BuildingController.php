<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Building;
use Modules\Educational\Domain\Models\Campus;
use Modules\Core\Application\Services\ApprovalService;

class BuildingController extends Controller
{
    public function index(Request $request)
    {
        $query = Building::with('campus')->latest();

        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $buildings = $query->get();
        $campuses = Campus::all();

        return view('educational::buildings.index', compact('buildings', 'campuses'));
    }

    public function create()
    {
        $campuses = Campus::where('status', 'active')->get();
        return view('educational::buildings.create', compact('campuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'campus_id' => 'required|exists:' . Campus::class . ',id',
            'name' => 'required|string|max:255',
            'code' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);

        Building::create($validated);

        return redirect()->route('educational.buildings.index')->with('success', 'Building created successfully.');
    }

    public function edit($id)
    {
        $building = Building::findOrFail($id);
        $campuses = Campus::where('status', 'active')->get();
        return view('educational::buildings.edit', compact('building', 'campuses'));
    }

    public function update(Request $request, $id)
    {
        $building = Building::findOrFail($id);

        $validated = $request->validate([
            'campus_id' => 'required|exists:' . Campus::class . ',id',
            'name' => 'required|string|max:255',
            'code' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);

        $building->update($validated);

        return redirect()->route('educational.buildings.index')->with('success', 'Building updated successfully.');
    }

    public function destroy($id, ApprovalService $approvalService)
    {
        $building = Building::findOrFail($id);

        if ($building->pendingApprovalRequest()) {
            return redirect()->back()->with('error', 'يوجد طلب معلق لحذف هذا المبنى بالفعل.');
        }

        try {
            $approvalService->requestApproval(
                approvable: $building,
                schema: 'education',
                action: 'delete',
                metadata: [
                    'name' => $building->name ?? 'مبنى بدون اسم',
                    'code' => $building->code ?? 'بدون كود',
                    'campus_name' => $building->campus->name ?? 'غير محدد',
                    'reason' => 'حذف المبنى'
                ],
                levels: 1
            );

            return redirect()->route('educational.buildings.index')->with('success', 'تم إرسال طلب الحذف للمراجعة والموافقة.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'فشل في إرسال طلب الحذف: ' . $e->getMessage());
        }
    }
}

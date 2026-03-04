<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Room;
use Modules\Educational\Domain\Models\Floor;
use Modules\Educational\Domain\Models\Building;
use Modules\Educational\Domain\Models\RoomType;
use Modules\Core\Application\Services\ApprovalService;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('floor.building.campus')->latest();

        if ($request->filled('building_id')) {
            $query->whereHas('floor.building', function ($q) use ($request) {
                $q->where('id', $request->building_id);
            });
        }

        if ($request->filled('room_status')) {
            $query->where('room_status', $request->room_status);
        }

        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        $rooms = $query->get();
        $buildings = Building::where('status', 'active')->get();
        $room_types = RoomType::active()->get();

        return view('educational::rooms.index', compact('rooms', 'buildings', 'room_types'));
    }

    public function create()
    {
        $buildings = Building::with('floors')->where('status', 'active')->get();
        $room_types = RoomType::active()->get();
        return view('educational::rooms.create', compact('buildings', 'room_types'));
    }

    public function store(Request $request)
    {
        $validRoomTypeSlugs = RoomType::active()->pluck('slug')->toArray();

        $validated = $request->validate([
            'floor_id' => 'required|exists:' . Floor::class . ',id',
            'name' => 'required|string|max:255',
            'code' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'room_type' => 'required|in:' . implode(',', $validRoomTypeSlugs),
            'room_status' => 'required|in:active,maintenance,disabled',
        ]);

        // Link room_type_id from slug
        $roomType = RoomType::where('slug', $validated['room_type'])->first();
        $validated['room_type_id'] = $roomType?->id;

        Room::create($validated);

        return redirect()->route('educational.rooms.index')->with('success', __('educational::messages.room_created') ?? 'تم إنشاء القاعة بنجاح.');
    }

    public function edit($id)
    {
        $room = Room::findOrFail($id);
        $buildings = Building::with('floors')->get();
        $room_types = RoomType::active()->get();
        return view('educational::rooms.edit', compact('room', 'buildings', 'room_types'));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validRoomTypeSlugs = RoomType::active()->pluck('slug')->toArray();

        $validated = $request->validate([
            'floor_id' => 'required|exists:' . Floor::class . ',id',
            'name' => 'required|string|max:255',
            'code' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'room_type' => 'required|in:' . implode(',', $validRoomTypeSlugs),
            'room_status' => 'required|in:active,maintenance,disabled',
        ]);

        // Link room_type_id from slug
        $roomType = RoomType::where('slug', $validated['room_type'])->first();
        $validated['room_type_id'] = $roomType?->id;

        $room->update($validated);

        return redirect()->route('educational.rooms.index')->with('success', __('educational::messages.room_updated') ?? 'تم تحديث القاعة بنجاح.');
    }

    public function destroy($id, ApprovalService $approvalService)
    {
        $room = Room::findOrFail($id);

        if ($room->pendingApprovalRequest()) {
            return redirect()->back()->with('error', 'يوجد طلب معلق لحذف هذه القاعة بالفعل.');
        }

        try {
            $approvalService->requestApproval(
                approvable: $room,
                schema: 'education',
                action: 'delete',
                metadata: [
                    'name' => $room->name ?? 'قاعة بدون اسم',
                    'code' => $room->code ?? 'بدون كود',
                    'floor_number' => $room->floor->floor_number ?? 'غير محدد',
                    'reason' => 'حذف القاعة'
                ],
                levels: 1
            );

            return redirect()->route('educational.rooms.index')->with('success', 'تم إرسال طلب الحذف للمراجعة والموافقة.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'فشل في إرسال طلب الحذف: ' . $e->getMessage());
        }
    }
}

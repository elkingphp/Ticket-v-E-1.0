<?php

namespace Modules\Educational\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\Events\LectureStatusChanged;

class LectureController extends Controller
{
    /**
     * Get list of lectures (could be filtered by date, room, or instructor).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lecture::query();

        if ($request->has('starts_after')) {
            $query->where('starts_at', '>=', $request->starts_after);
        }

        return response()->json(['data' => $query->get()]);
    }

    /**
     * Update lecture status (e.g. from scheduled to cancelled or completed).
     */
    public function updateStatus(Request $request, Lecture $lecture): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:scheduled,running,completed,cancelled,rescheduled'
        ]);

        $oldStatus = $lecture->status;
        $newStatus = $request->status;

        if ($oldStatus !== $newStatus) {
            $lecture->update(['status' => $newStatus]);

            // Dispatch domain event which triggers Tickets/Notifications
            event(new LectureStatusChanged($lecture, $oldStatus, $newStatus));
        }

        return response()->json(['message' => 'Lecture status updated.', 'data' => $lecture]);
    }
}

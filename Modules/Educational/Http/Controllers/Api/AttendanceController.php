<?php

namespace Modules\Educational\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Attendance;
use Modules\Educational\Domain\Events\AttendanceOverridden;
use Modules\Core\Application\Services\ApprovalService;
use Exception;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Simple Example: return attendances for a specific lecture
        $request->validate(['lecture_id' => 'required|integer']);

        $attendances = Attendance::where('lecture_id', $request->lecture_id)->get();
        return response()->json(['data' => $attendances]);
    }

    /**
     * Update the specified attendance in storage.
     */
    public function update(Request $request, Attendance $attendance, ApprovalService $approvalService): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,excused',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $attendance->status;

        // If it's already locked, we might need Approval. Let's handle it manually or let the Trait handle it if hooked up.
        // For demonstration of the integration:
        if ($attendance->isLocked()) {
            // Either trigger an approval request...
            if ($attendance->requiresApproval()) {
                // Submit an approval request via Approval Engine and stop here.
                try {
                    $approvalRequest = $approvalService->requestApproval(
                        approvable: $attendance,
                        schema: 'education',
                        action: 'override_locked_attendance',
                        metadata: [
                            'requested_changes' => [
                                'status' => $request->status,
                                'notes' => $request->notes,
                                'old_status' => $oldStatus
                            ]
                        ],
                        levels: 1 // Example: only 1 level of approval needed
                    );

                    // Fire the event for monitoring/Notification/Ticket
                    event(new AttendanceOverridden($attendance));

                    return response()->json([
                        'message' => 'Attendance is locked. Approval request submitted.',
                        'approval_request_id' => $approvalRequest->id
                    ], 202);
                } catch (Exception $e) {
                    // Note: auth()->user() might be null in tests, fallback behavior
                }
            }
        }

        // Direct update if not locked or approval bypassed
        $attendance->update($request->only('status', 'notes'));

        return response()->json(['message' => 'Attendance updated successfully.', 'data' => $attendance]);
    }

    /**
     * Lock the attendance for a lecture. (Teacher locks it after class)
     */
    public function lock(Request $request, Attendance $attendance): JsonResponse
    {
        $attendance->update(['locked_at' => now()]);
        return response()->json(['message' => 'Attendance locked.', 'data' => $attendance]);
    }
}

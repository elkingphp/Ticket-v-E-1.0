<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Domain\Models\ApprovalRequest;
use Modules\Core\Application\Services\ApprovalService;

class TicketDeleteRequestController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.delete_requests.manage'),
        ];
    }

    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');

        $requests = ApprovalRequest::with('requester')
            ->where('approvable_type', $type);

        if ($id) {
            $requests->where('approvable_id', $id);
        }

        $requests = $requests->where('status', 'pending')
            ->where('action', 'delete')
            ->get();

        return response()->json($requests->map(function ($req) {
            $item = $req->approvable;

            if ($item instanceof \Modules\Tickets\Domain\Models\Ticket) {
                $itemName = "[#{$item->ticket_number}] {$item->subject}";
            } else {
                $itemName = $item ? ($item->name ?? ($item->title ?? $item->id)) : $req->approvable_id;
            }

            $modelName = class_basename($req->approvable_type);
            $translatedModel = __("core::messages.entities.{$modelName}") == "core::messages.entities.{$modelName}" ? $modelName : __("core::messages.entities.{$modelName}");

            return [
                'id' => $req->id,
                'user' => [
                    'name' => $req->requester->full_name ?? ($req->requester->name ?? 'User'),
                    'email' => $req->requester->email ?? '-',
                ],
                'item_name' => $itemName,
                'page' => $translatedModel,
                'created_at' => $req->created_at->format('Y-m-d h:i A'),
            ];
        }));
    }

    public function approve(Request $request, ApprovalRequest $deleteRequest)
    {
        try {
            $this->approvalService->approve($deleteRequest);

            // Check if the item was deleted (since the listener doesn't do it yet)
            $item = $deleteRequest->approvable;
            if ($item && $deleteRequest->status === 'approved') {
                $item->delete();
            }

            return redirect()->back()->with('success', __('tickets::messages.request_approved'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, ApprovalRequest $deleteRequest)
    {
        try {
            $this->approvalService->reject($deleteRequest);
            return redirect()->back()->with('success', __('tickets::messages.request_rejected'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

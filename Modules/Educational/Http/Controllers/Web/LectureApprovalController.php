<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Application\Services\ApprovalService;
use Modules\Core\Domain\Models\ApprovalRequest;
use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\State\LectureStateMachine;

class LectureApprovalController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display a listing of pending lecture requests.
     */
    public function index()
    {
        $this->authorizeAdmin();

        $requests = ApprovalRequest::with(['approvable', 'requester'])
            ->where('approvable_type', Lecture::class)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('modules.educational.lectures.approvals', compact('requests'));
    }

    /**
     * Approve a lecture request.
     */
    public function approve(Request $request, $id)
    {
        $this->authorizeAdmin();

        $approvalRequest = ApprovalRequest::findOrFail($id);

        try {
            $approvalRequest = $this->approvalService->approve($approvalRequest, $request->comments);

            $msg = 'تم تسجيل موافقتك بنجاح.';

            // Execute the actual action ONLY if the whole request is approved
            if ($approvalRequest->status === 'approved') {
                $lecture = $approvalRequest->approvable;

                if (!$lecture) {
                    throw new \Exception('المحاضرة المرتبطة بهذا الطلب لم تعد موجودة.');
                }

                if ($approvalRequest->action === 'delete_lecture') {
                    $lecture->delete();
                    $msg = 'تم مراجعة وحذف المحاضرة بنجاح.';
                } elseif ($approvalRequest->action === 'cancel_lecture') {
                    $sm = new LectureStateMachine($lecture);
                    $sm->transitionToCancelled();
                    $msg = 'تم مراجعة وإلغاء المحاضرة بنجاح.';
                }
            } else {
                $msg = 'تم تسجيل موافقتك. بانتظار بقية مستويات المراجعة.';
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'فشل الإجراء: ' . $e->getMessage());
        }
    }

    /**
     * Reject a lecture request.
     */
    public function reject(Request $request, $id)
    {
        $this->authorizeAdmin();

        $approvalRequest = ApprovalRequest::findOrFail($id);

        try {
            $this->approvalService->reject($approvalRequest, $request->comments);
            return back()->with('success', 'تم رفض الطلب بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل الإجراء: ' . $e->getMessage());
        }
    }

    private function authorizeAdmin()
    {
        abort_if(!auth()->user()->hasRole('super-admin'), 403, 'غير مصرح لك بالوصول لهذه الصفحة.');
    }
}

<?php

namespace Modules\Core\Application\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Domain\Events\ApprovalApproved;
use Modules\Core\Domain\Events\ApprovalRejected;
use Modules\Core\Domain\Events\ApprovalRequested;
use Modules\Core\Domain\Models\ApprovalRequest;

class ApprovalService
{
    /**
     * Request a new approval.
     */
    public function requestApproval($approvable, string $schema, string $action, array $metadata = [], int $levels = 1, array $permissions = []): ApprovalRequest
    {
        return DB::transaction(function () use ($approvable, $schema, $action, $metadata, $levels, $permissions) {
            $request = ApprovalRequest::create([
                'approvable_schema' => $schema,
                'approvable_type' => get_class($approvable),
                'approvable_id' => $approvable->id,
                'action' => $action,
                'status' => 'pending',
                'requested_by' => auth()->id(),
                'approval_level' => 1,
                'metadata' => $metadata,
            ]);

            // Create steps
            for ($i = 1; $i <= $levels; $i++) {
                $request->steps()->create([
                    'step_level' => $i,
                    'required_permission' => $permissions[$i - 1] ?? null,
                    'status' => 'pending',
                ]);
            }

            event(new ApprovalRequested($request));

            return $request;
        });
    }

    /**
     * Approve a pending request.
     */
    public function approve(ApprovalRequest $request, ?string $comments = null): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $comments) {
            if (!$request->isPending()) {
                throw new Exception("الطلب ليس في حالة انتظار.");
            }

            // Find current pending step and lock it
            $currentStep = $request->steps()
                ->where('status', 'pending')
                ->orderBy('step_level', 'asc')
                ->lockForUpdate()
                ->first();

            /** @var \Modules\Core\Domain\Models\ApprovalStep|null $currentStep */
            if ($currentStep) {
                $currentStep->update([
                    'status' => 'approved',
                    'acted_by' => auth()->id(),
                    'comments' => $comments,
                    'acted_at' => now(),
                ]);

                // Check if there are more pending steps
                $nextStep = $request->steps()
                    ->where('status', 'pending')
                    ->where('step_level', '>', $currentStep->step_level)
                    ->first();

                if ($nextStep) {
                    // Move request level to the next step — not fully approved yet
                    $request->update(['approval_level' => $nextStep->step_level]);
                    return $request->fresh();
                }
            }

            // No pending steps remain (or request has no steps) — finalize approval
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            event(new ApprovalApproved($request));

            return $request->fresh();
        });
    }

    /**
     * Reject a pending request.
     */
    public function reject(ApprovalRequest $request, ?string $comments = null): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $comments) {
            if (!$request->isPending()) {
                throw new Exception("الطلب ليس في حالة انتظار.");
            }

            $currentStep = $request->steps()
                ->where('status', 'pending')
                ->orderBy('step_level', 'asc')
                ->lockForUpdate()
                ->first();

            /** @var \Modules\Core\Domain\Models\ApprovalStep $currentStep */

            if ($currentStep) {
                $currentStep->update([
                    'status' => 'rejected',
                    'acted_by' => auth()->id(),
                    'comments' => $comments,
                    'acted_at' => now(),
                ]);
            }

            $request->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
            ]);

            event(new ApprovalRejected($request));

            return $request->fresh();
        });
    }
}

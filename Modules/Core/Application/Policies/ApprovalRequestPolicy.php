<?php

namespace Modules\Core\Application\Policies;

use Modules\Users\Domain\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Domain\Models\ApprovalRequest;

class ApprovalRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Requires a generic permission or is Super Admin
        return $user->hasPermissionTo('core.approvals.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Users can view their own requests, or reviewers can view
        return $user->id === $approvalRequest->requested_by ||
            $user->hasPermissionTo('core.approvals.view');
    }

    /**
     * Determine whether the user can approve the request.
     */
    public function approve(User $user, ApprovalRequest $approvalRequest): bool
    {
        $currentStep = $approvalRequest->steps()
            ->where('status', 'pending')
            ->orderBy('step_level', 'asc')
            ->first();

        // If no pending step found, no one can approve
        if (!$currentStep) {
            return false;
        }

        // If the current step demands a specific permission
        if (!empty($currentStep->required_permission)) {
            return $user->hasPermissionTo($currentStep->required_permission);
        }

        // Fallback: If no custom permission was provided to the step, default to a generic manage right
        return $user->hasPermissionTo('core.approvals.manage');
    }

    /**
     * Determine whether the user can reject the request.
     */
    public function reject(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Same logic as approve for now: if you can approve, you can reject
        return $this->approve($user, $approvalRequest);
    }
}

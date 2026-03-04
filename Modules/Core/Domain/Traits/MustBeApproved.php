<?php

namespace Modules\Core\Domain\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Core\Domain\Models\ApprovalRequest;

trait MustBeApproved
{
    /**
     * Get all of the model's approval requests.
     */
    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    /**
     * Helper to get the active/pending approval request.
     */
    public function pendingApprovalRequest(): ?ApprovalRequest
    {
        return $this->approvalRequests()->where('status', 'pending')->first();
    }

    /**
     * Determine if this model requires approval for any action right now.
     */
    public function requiresApproval(): bool
    {
        return $this->pendingApprovalRequest() !== null;
    }
}

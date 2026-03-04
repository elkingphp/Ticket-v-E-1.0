<?php

namespace Modules\Core\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Domain\Models\ApprovalRequest;

class ApprovalRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $approvalRequest;

    public function __construct(ApprovalRequest $approvalRequest)
    {
        $this->approvalRequest = $approvalRequest;
    }
}

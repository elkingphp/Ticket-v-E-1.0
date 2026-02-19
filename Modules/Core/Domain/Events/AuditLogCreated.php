<?php

namespace Modules\Core\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Domain\Models\AuditLog;

class AuditLogCreated
{
    use Dispatchable, SerializesModels;

    public AuditLog $auditLog;

    public function __construct(AuditLog $auditLog)
    {
        $this->auditLog = $auditLog;
    }
}
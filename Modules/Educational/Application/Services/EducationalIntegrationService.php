<?php

namespace Modules\Educational\Application\Services;

use Modules\Core\Application\Services\AuditLoggerService;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Domain\Models\TicketStatus;
use Illuminate\Support\Facades\DB;

/**
 * Class EducationalIntegrationService
 * 
 * Acts as an Orchestration/Anti-Corruption Layer (ACL) between Educational Module 
 * and external modules like Tickets or Notifications.
 */
class EducationalIntegrationService
{
    protected $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Raise a support ticket for administrative intervention.
     */
    public function raiseSupportTicket(string $title, string $message, string $priority = 'high'): void
    {
        // Decoupled logic for ticket creation
        // We catch exceptions to ensure educational flow isn't broken by external failures
        try {
            if (class_exists(Ticket::class) && class_exists(TicketStatus::class)) {
                $status = TicketStatus::where('is_default', true)->first() ?? TicketStatus::first();

                Ticket::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'ticket_number' => 'EDU-' . time(),
                    'user_id' => auth()->id() ?? 1,
                    'subject' => $title,
                    'details' => $message,
                    'status_id' => $status?->id,
                    'priority_id' => 1, // Fallback priority
                    'module' => 'educational'
                ]);
            }
        } catch (\Exception $e) {
            $this->auditLogger->log('integration_error', 'educational', null, ['error' => $e->getMessage()], 'error');
        }
    }

    /**
     * Dispatch multi-channel notifications (Database, Mail, etc.)
     */
    public function notifyStaff(string $title, string $message, string $level = 'info'): void
    {
        // Logic for resolved recipients would go here
        $this->auditLogger->log('notification_dispatched', 'educational', null, ['title' => $title, 'message' => $message], $level);
    }

    /**
     * Centralized logging for Educational events
     */
    public function logEvent(string $category, string $description, $subject = null): void
    {
        $this->auditLogger->log($category, 'educational', null, ['description' => $description, 'subject' => $subject]);
    }
}

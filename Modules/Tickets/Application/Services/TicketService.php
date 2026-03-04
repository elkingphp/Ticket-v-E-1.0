<?php

namespace Modules\Tickets\Application\Services;

use Modules\Core\Domain\Models\SystemSetting;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Domain\Models\TicketStage;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Tickets\Domain\Models\TicketComplaint;
use Modules\Tickets\Domain\Models\TicketRouting;
use Modules\Tickets\Domain\Models\TicketGroup;
use Modules\Tickets\Domain\Models\TicketStatus;
use Modules\Tickets\Domain\Models\TicketPriority;
use Modules\Tickets\Events\TicketCreated;
use Modules\Tickets\Events\TicketReplyCreated;
use Modules\Tickets\Events\TicketStatusChanged;
use Modules\Users\Domain\Models\User;
use Modules\Tickets\Services\TicketActivityService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TicketService
{
    protected $activityService;

    public function __construct(TicketActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function generateTicketNumber(): string
    {
        $format = get_setting('tickets_number_format', 'TICK-{ID}');

        $lastId = Ticket::max('id') ?? 0;
        $nextId = $lastId + 1;

        // Support dynamic padding for {ID} using # prefix (e.g., ###{ID} for 4 digits)
        $format = preg_replace_callback('/(#*)\{ID\}/', function ($m) use ($nextId) {
            $hashes = $m[1];
            if (strlen($hashes) > 0) {
                return str_pad($nextId, strlen($hashes) + 1, '0', STR_PAD_LEFT);
            }
            return str_pad($nextId, 6, '0', STR_PAD_LEFT); // Default padding of 6
        }, $format);

        $replacements = [
            '{YY}' => date('y'),
            '{YYYY}' => date('Y'),
            '{MM}' => date('m'),
            '{DD}' => date('d'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }
    public function createTicket(User $user, array $data): Ticket
    {
        $stage = TicketStage::find($data['stage_id']);
        $category = TicketCategory::find($data['category_id']);
        $complaint = isset($data['complaint_id']) ? TicketComplaint::find($data['complaint_id']) : null;

        // Calculate Due Date
        $dueAt = $this->calculateDueAt($stage, $category, $complaint);

        // Resolve Assigned Group
        $assignedGroup = $this->resolveAssignedGroup($stage, $category, $complaint);

        // Get Default Status
        $status = TicketStatus::where('is_default', true)->first() ?? TicketStatus::first();

        // Generate Ticket Number
        $ticketNumber = $this->generateTicketNumber();

        // Create Ticket
        $ticket = Ticket::create([
            'uuid' => Str::uuid(),
            'ticket_number' => $ticketNumber,
            'user_id' => $user->id,
            'subject' => $data['subject'],
            'details' => $data['details'],
            'status_id' => $status->id,
            'priority_id' => $data['priority_id'],
            'stage_id' => $stage->id,
            'category_id' => $category->id,
            'complaint_id' => $complaint ? $complaint->id : null,
            'assigned_group_id' => $assignedGroup ? $assignedGroup->id : null,
            'due_at' => $dueAt,
            'lecture_id' => !empty($data['lecture_id']) ? $data['lecture_id'] : null,
        ]);

        // Sync Sub-Complaints
        if (isset($data['sub_complaints']) && is_array($data['sub_complaints'])) {
            $ticket->subComplaints()->sync($data['sub_complaints']);
        }

        // Handle Attachments
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $path = $file->store('tickets/attachments', 'public');
                $ticket->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Centralized Log & Notification
        $this->activityService->record($ticket, 'created', [
            'actor_name' => $user->full_name ?? $user->name
        ]);

        return $ticket;
    }

    public function addReply(Ticket $ticket, User $user, string $content, array $attachments = [], bool $isInternal = false)
    {
        $type = $isInternal ? 'internal_note' : 'message';

        $thread = $ticket->threads()->create([
            'user_id' => $user->id,
            'content' => $content,
            'type' => $type,
        ]);

        if (!empty($attachments)) {
            foreach ($attachments as $file) {
                $path = $file->store('tickets/attachments', 'public');
                $thread->attachments()->create([
                    'ticket_id' => $ticket->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        $ticket->touch();

        // Centralized Log & Notification
        $this->activityService->record($ticket, 'replied', [
            'is_internal' => $isInternal,
            'actor_name' => $user->full_name ?? $user->name
        ]);

        return $thread;
    }

    public function updateStatus(Ticket $ticket, TicketStatus $newStatus, User $user)
    {
        $oldStatus = $ticket->status;

        if ($oldStatus->id !== $newStatus->id) {
            $ticket->status_id = $newStatus->id;
            $ticket->save();

            // Centralized Log & Notification
            $this->activityService->record($ticket, 'status_changed', [
                'old_status_name' => $oldStatus->name,
                'new_status_name' => $newStatus->name,
                'actor_name' => $user->full_name ?? $user->name
            ]);
        }

        return $ticket;
    }

    /**
     * Calculate the Due Date based on SLA inheritance.
     * Complaint SLA > Category SLA > Stage SLA
     */
    public function calculateDueAt(?TicketStage $stage, ?TicketCategory $category, ?TicketComplaint $complaint): ?Carbon
    {
        $slaHours = null;

        if ($complaint && $complaint->sla_hours) {
            $slaHours = $complaint->sla_hours;
        } elseif ($category && $category->sla_hours) {
            $slaHours = $category->sla_hours;
        } elseif ($stage && $stage->sla_hours) {
            $slaHours = $stage->sla_hours;
        }

        if ($slaHours) {
            // Logic can be enhanced to support business hours (skip weekends/nights)
            return now()->addHours($slaHours);
        }

        return null;
    }

    /**
     * Resolve the Assigned Group based on Routing Hierarchy.
     * Complaint Group > Category Group > Stage Group > Default Group
     */
    public function resolveAssignedGroup(?TicketStage $stage, ?TicketCategory $category, ?TicketComplaint $complaint): ?TicketGroup
    {
        // Check Complaint Routing
        if ($complaint) {
            $routing = TicketRouting::where('entity_type', TicketComplaint::class)
                ->where('entity_id', $complaint->id)
                ->first();
            if ($routing)
                return $routing->group;
        }

        // Check Category Routing
        if ($category) {
            $routing = TicketRouting::where('entity_type', TicketCategory::class)
                ->where('entity_id', $category->id)
                ->first();
            if ($routing)
                return $routing->group;
        }

        // Check Stage Routing
        if ($stage) {
            $routing = TicketRouting::where('entity_type', TicketStage::class)
                ->where('entity_id', $stage->id)
                ->first();
            if ($routing)
                return $routing->group;
        }

        // Return Default Group
        return TicketGroup::where('is_default', true)->first();
    }
}

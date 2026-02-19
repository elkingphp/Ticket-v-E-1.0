<?php

namespace Modules\Tickets\Services;

use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Domain\Models\TicketActivity;
use Modules\Tickets\Events\TicketActionEvent;
use Illuminate\Support\Facades\Auth;

class TicketActivityService
{
    /**
     * Record a ticket activity and fire the action event.
     *
     * @param Ticket $ticket
     * @param string $action
     * @param array $meta
     * @return TicketActivity
     */
    public function record(Ticket $ticket, string $action, array $meta = [])
    {
        $actor = Auth::user();

        // 1. Create the Audit Log
        $activity = TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $actor ? $actor->id : null,
            'activity_type' => $action,
            'description' => $this->generateDescription($action, $meta),
            'properties' => $meta,
        ]);

        // 2. Fire the Central Action Event
        event(new TicketActionEvent($ticket, $action, $meta, $actor));

        return $activity;
    }

    /**
     * Generate a human-readable description for the activity log.
     */
    protected function generateDescription(string $action, array $meta): string
    {
        $params = [];
        if (isset($meta['new_status_name']))
            $params['status'] = $meta['new_status_name'];
        if (isset($meta['agent_name']))
            $params['agent'] = $meta['agent_name'];
        if (isset($meta['group_name']))
            $params['group'] = $meta['group_name'];

        return __('tickets::messages.activities.' . $action, $params);
    }
}

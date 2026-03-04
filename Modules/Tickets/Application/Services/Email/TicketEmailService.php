<?php

namespace Modules\Tickets\Application\Services\Email;

use Illuminate\Support\Facades\Mail;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Domain\Models\TicketEmailTemplate;
use Modules\Tickets\Domain\Models\TicketThread;

class TicketEmailService
{
    /**
     * Notify the customer.
     */
    public function notifyCustomer(string $eventKey, Ticket $ticket, ?TicketThread $thread = null)
    {
        $this->sendToUser($ticket->user->email, $eventKey, $ticket, $thread);
    }

    /**
     * Notify the assigned agent or group members.
     */
    public function notifyAgents(string $eventKey, Ticket $ticket, ?TicketThread $thread = null)
    {
        $recipients = [];

        // If assigned to a specific agent
        if ($ticket->assignedTo) {
            $recipients[] = $ticket->assignedTo->email;
        }
        // If assigned to a group but no specific agent, notify all group members
        elseif ($ticket->assignedGroup) {
            $recipients = $ticket->assignedGroup->members()->pluck('email')->toArray();
        }

        foreach (array_unique($recipients) as $email) {
            $this->sendToUser($email, $eventKey, $ticket, $thread);
        }
    }

    /**
     * Low level sending logic.
     */
    protected function sendToUser(string $email, string $eventKey, Ticket $ticket, ?TicketThread $thread = null)
    {
        $template = TicketEmailTemplate::where('event_key', $eventKey)->first();

        if (!$template) {
            return;
        }

        $subject = $this->parseContent($template->subject, $ticket, $thread);
        $body = $this->parseContent($template->body, $ticket, $thread);

        // Use HTML mail
        Mail::send([], [], function ($message) use ($email, $subject, $body) {
            $message->to($email)
                ->subject($subject)
                ->html($body);
        });
    }

    protected function parseContent(string $content, Ticket $ticket, ?TicketThread $thread = null): string
    {
        $replacements = [
            'ticket_id' => $ticket->uuid,
            'ticket_number' => $ticket->ticket_number,
            'id' => substr($ticket->uuid, 0, 8),
            'subject' => $ticket->subject,
            'status' => $ticket->status?->name,
            'priority' => $ticket->priority?->name,
            'assignee' => $ticket->assignedTo?->name ?? 'Unassigned',
            'customer_name' => $ticket->user->name,
            'user_name' => $ticket->user->name,
            'link' => route('tickets.show', $ticket->uuid),
            'url' => route('tickets.show', $ticket->uuid),
            'agent_link' => route('agent.tickets.show', $ticket->uuid),
            'logo' => get_setting('logo_light') ? asset(get_setting('logo_light')) : asset('assets/images/logo-dark.png'),
            'app_name' => get_setting('site_name', 'Digilians'),
        ];

        if ($thread) {
            $replacements['message'] = $thread->content;
            $replacements['reply'] = $thread->content;
        }

        foreach ($replacements as $key => $value) {
            $content = str_replace(["{{ $key }}", "{{$key}}", "{ $key }", "{$key}"], (string) ($value ?? ''), $content);
        }

        return $content;
    }
}

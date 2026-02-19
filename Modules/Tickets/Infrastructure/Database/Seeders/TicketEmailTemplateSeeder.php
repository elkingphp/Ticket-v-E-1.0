<?php

namespace Modules\Tickets\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Tickets\Domain\Models\TicketEmailTemplate;

class TicketEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            [
                'name' => 'Ticket Created (To Customer)',
                'event_key' => 'ticket_created',
                'subject' => 'Ticket #{{ ticket_id }} Created: {{ subject }}',
                'body' => "Hi {{ customer_name }},\n\nThank you for reaching out. We have received your ticket regarding \"{{ subject }}\".\n\nTicket ID: {{ ticket_id }}\nStatus: {{ status }}\n\nYou can track the status here: {{ link }}\n\nWe will get back to you shortly.\n\nRegards,\nSupport Team",
                'is_active' => true,
            ],
            [
                'name' => 'New Reply (To Customer)',
                'event_key' => 'ticket_replied',
                'subject' => 'New Reply on Ticket #{{ ticket_id }}',
                'body' => "Hi {{ customer_name }},\n\nA new reply has been added to your ticket #{{ ticket_id }}:\n\n--------------------------\n{{ message }}\n--------------------------\n\nView the thread here: {{ link }}\n\nRegards,\nSupport Team",
                'is_active' => true,
            ],
            [
                'name' => 'Ticket Status Changed',
                'event_key' => 'ticket_status_changed',
                'subject' => 'Ticket #{{ ticket_id }} Status Updated',
                'body' => "Hi {{ customer_name }},\n\nThe status of your ticket #{{ ticket_id }} has been updated to: {{ status }}.\n\nView details: {{ link }}\n\nRegards,\nSupport Team",
                'is_active' => true,
            ],
            [
                'name' => 'New Ticket Assigned (To Agent)',
                'event_key' => 'agent_ticket_assigned',
                'subject' => 'New Ticket Assigned: #{{ ticket_id }}',
                'body' => "A new ticket has been assigned to your group.\n\nSubject: {{ subject }}\nCustomer: {{ customer_name }}\nPriority: {{ priority }}\n\nView and handle it here: {{ agent_link }}",
                'is_active' => true,
            ],
            [
                'name' => 'Customer Replied (To Agent)',
                'event_key' => 'agent_customer_reply',
                'subject' => 'Customer Replied to Ticket #{{ ticket_id }}',
                'body' => "Customer {{ customer_name }} has replied to ticket #{{ ticket_id }}.\n\nMessage:\n--------------------------\n{{ message }}\n--------------------------\n\nView thread: {{ agent_link }}",
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            TicketEmailTemplate::updateOrCreate(
                ['event_key' => $template['event_key']],
                [
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                ]
            );
        }
    }
}

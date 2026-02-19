<?php

namespace Modules\Tickets\Tests\Feature;

use Tests\TestCase;
use Modules\Users\Domain\Models\User;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Domain\Models\TicketStage;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Tickets\Domain\Models\TicketComplaint;
use Modules\Tickets\Domain\Models\TicketGroup;
use Modules\Tickets\Domain\Models\TicketPriority;
use Modules\Tickets\Domain\Models\TicketStatus;
use Modules\Tickets\Domain\Models\TicketRouting;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class TicketFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $stage;
    protected $category;
    protected $complaint;
    protected $priority;
    protected $status;
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary roles/permissions if required by User factory
        // Or just create user
        $this->user = User::factory()->create([
            'password' => 'password', // plaintext, let model cast hash it
        ]);

        // Setup prerequisites
        $this->stage = TicketStage::create(['name' => 'Support Stage']);
        $this->category = TicketCategory::create(['name' => 'Technical', 'stage_id' => $this->stage->id]);
        $this->complaint = TicketComplaint::create(['name' => 'Bug', 'category_id' => $this->category->id]);
        $this->group = TicketGroup::create(['name' => 'Tech Support', 'is_default' => true]);

        $this->priority = TicketPriority::create(['name' => 'Normal', 'color' => 'success', 'is_default' => true]);
        $this->status = TicketStatus::create(['name' => 'Open', 'color' => 'success', 'is_default' => true]);

        // Setup Routing (complaint -> group)
        TicketRouting::create([
            'entity_type' => TicketComplaint::class,
            'entity_id' => $this->complaint->id,
            'group_id' => $this->group->id,
        ]);
    }

    /** @test */
    public function user_can_create_ticket_with_automatic_routing()
    {
        Event::fake();

        $response = $this->actingAs($this->user)->post(route('tickets.store'), [
            'subject' => 'Test Ticket Subject',
            'description' => 'Test Ticket Description',
            'stage_id' => $this->stage->id,
            'category_id' => $this->category->id,
            'complaint_id' => $this->complaint->id,
            'priority_id' => $this->priority->id,
        ]);

        $response->assertRedirect();

        // Check DB
        $ticket = Ticket::where('subject', 'Test Ticket Subject')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($this->user->id, $ticket->user_id);
        $this->assertEquals($this->group->id, $ticket->assigned_group_id); // Routing check
        $this->assertEquals($this->status->id, $ticket->status_id);

        Event::assertDispatched(\Modules\Tickets\Events\TicketCreated::class);
    }

    /** @test */
    public function user_can_reply_to_ticket()
    {
        Event::fake();

        $ticket = Ticket::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $this->user->id,
            'subject' => 'Existing Ticket',
            'details' => 'Details',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'stage_id' => $this->stage->id,
            'category_id' => $this->category->id,
            'assigned_group_id' => $this->group->id,
            'due_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->user)->put(route('tickets.update', $ticket->uuid), [
            'message' => 'This is a reply.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tickets.ticket_threads', [
            'ticket_id' => $ticket->id,
            'content' => 'This is a reply.',
            'user_id' => $this->user->id,
            'type' => 'message',
        ]);

        Event::assertDispatched(\Modules\Tickets\Events\TicketReplyCreated::class);
    }

    /** @test */
    public function agent_can_view_assigned_tickets()
    {
        $agent = User::factory()->create(['password' => 'password']);
        // Add agent to group
        $this->group->members()->attach($agent->id);

        $ticket = Ticket::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $this->user->id,
            'subject' => 'Group Ticket',
            'details' => 'Details',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'stage_id' => $this->stage->id,
            'category_id' => $this->category->id,
            'assigned_group_id' => $this->group->id,
            'due_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($agent)->get(route('agent.tickets.index'));

        $response->assertStatus(200);
        $response->assertSee('Group Ticket');
    }
}

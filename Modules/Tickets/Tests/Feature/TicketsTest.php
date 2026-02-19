<?php

namespace Modules\Tickets\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Modules\Users\Domain\Models\User;
use Modules\Tickets\Domain\Models\TicketStage;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Tickets\Domain\Models\TicketComplaint;
use Modules\Tickets\Domain\Models\TicketPriority;
use Modules\Tickets\Domain\Models\TicketStatus;
use Modules\Tickets\Domain\Models\TicketGroup;
use Modules\Tickets\Domain\Models\Ticket;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Modules\Tickets\Events\TicketCreated;
use Illuminate\Http\UploadedFile;

class TicketsTest extends TestCase
{
    // Use RefreshDatabase if testing with DB reset, but usually for feature tests of existing app we might not want to wipe unless using specific DB connection.
    // Given the environment, I'll avoid RefreshDatabase and clean up manually or assume test DB is isolated.
    // However, for correctness, let's use DatabaseTransactions if available or just proceed carefully.
    // I'll assume standard Laravel testing setup. If no separate DB, RefreshDatabase wipes actual DB!
    // Since this is a dev env, I should be careful. I'll rely on creating unique data.

    public function test_user_can_view_tickets_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tickets.index'));

        $response->assertStatus(200);
        $response->assertSee('My Tickets'); // Assuming this text exists
    }

    public function test_user_can_create_ticket()
    {
        Event::fake([TicketCreated::class]);

        $user = User::factory()->create();

        // Prerequisites
        $stage = TicketStage::firstOrCreate(['name' => 'General Inquiry']);
        $category = TicketCategory::firstOrCreate(['name' => 'Support', 'stage_id' => $stage->id]);
        $complaint = TicketComplaint::firstOrCreate(['name' => 'Login Issue', 'category_id' => $category->id]);
        $priority = TicketPriority::firstOrCreate(['name' => 'Normal', 'color' => 'success', 'is_default' => true]);
        $group = TicketGroup::firstOrCreate(['name' => 'Level 1 Support', 'is_default' => true]);
        $status = TicketStatus::firstOrCreate(['name' => 'Open', 'is_default' => true]);

        $data = [
            'subject' => 'Cannot login to my account',
            'description' => 'I forgot my password and reset fails.',
            'stage_id' => $stage->id,
            'category_id' => $category->id,
            'complaint_id' => $complaint->id,
            'priority_id' => $priority->id,
            // 'attachments' ...
        ];

        $response = $this->actingAs($user)->post(route('tickets.store'), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('tickets.tickets', [
            'subject' => 'Cannot login to my account',
            'user_id' => $user->id,
            'stage_id' => $stage->id,
            'priority_id' => $priority->id,
        ]);

        Event::assertDispatched(TicketCreated::class);
    }

    public function test_ticket_routing_logic()
    {
        // ...
    }
}

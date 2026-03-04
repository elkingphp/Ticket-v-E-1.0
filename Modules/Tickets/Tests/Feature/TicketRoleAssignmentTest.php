<?php

namespace Modules\Tickets\Tests\Feature;

use Tests\TestCase;
use Modules\Users\Domain\Models\User;
use Modules\Users\Domain\Models\Role;
use Modules\Tickets\Domain\Models\TicketStage;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Tickets\Domain\Models\TicketComplaint;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_ticket_stage_with_roles()
    {
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole($adminRole);
        $roleToAssign = Role::firstOrCreate(['name' => 'students', 'guard_name' => 'web']);

        $data = [
            'name' => 'Test Stage with Role',
            'external_name' => 'External Stage Name',
            'sla_hours' => 24,
            'roles' => [$roleToAssign->id],
        ];

        $response = $this->actingAs($admin)->post(route('admin.tickets.stages.store'), $data);

        $response->assertRedirect(route('admin.tickets.stages.index'));

        $this->assertDatabaseHas('tickets.ticket_stages', [
            'name' => 'Test Stage with Role',
            'external_name' => 'External Stage Name',
            'sla_hours' => 24,
        ]);

        $stage = TicketStage::where('name', 'Test Stage with Role')->first();
        $this->assertTrue($stage->roles->contains($roleToAssign));
    }

    public function test_admin_can_create_ticket_category_with_roles()
    {
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole($adminRole);

        $stage = TicketStage::firstOrCreate(['name' => 'Test Stage'], ['roles' => []]);
        $roleToAssign = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $data = [
            'name' => 'Test Category with Role',
            'stage_id' => $stage->id,
            'roles' => [$roleToAssign->id]
        ];

        $response = $this->actingAs($admin)->post(route('admin.tickets.categories.store'), $data);

        $response->assertRedirect(route('admin.tickets.categories.index'));

        $this->assertDatabaseHas('tickets.ticket_categories', [
            'name' => 'Test Category with Role',
            'stage_id' => $stage->id,
        ]);

        $category = TicketCategory::where('name', 'Test Category with Role')->first();
        $this->assertTrue($category->roles->contains($roleToAssign));
    }

    public function test_admin_can_create_ticket_complaint_with_roles()
    {
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole($adminRole);

        $stage = TicketStage::firstOrCreate(['name' => 'Test Stage'], ['roles' => []]);
        $category = TicketCategory::firstOrCreate(['name' => 'Test Category', 'stage_id' => $stage->id]);
        $roleToAssign = Role::firstOrCreate(['name' => 'complaints_team', 'guard_name' => 'web']);

        $data = [
            'name' => 'Test Complaint with Role',
            'category_id' => $category->id,
            'roles' => [$roleToAssign->id]
        ];

        $response = $this->actingAs($admin)->post(route('admin.tickets.complaints.store'), $data);

        $response->assertRedirect(route('admin.tickets.complaints.index'));

        $this->assertDatabaseHas('tickets.ticket_complaints', [
            'name' => 'Test Complaint with Role',
            'category_id' => $category->id,
        ]);

        $complaint = TicketComplaint::where('name', 'Test Complaint with Role')->first();
        $this->assertTrue($complaint->roles->contains($roleToAssign));
    }
}

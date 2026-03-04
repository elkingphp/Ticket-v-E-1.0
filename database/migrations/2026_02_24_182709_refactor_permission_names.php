<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    private array $mapping = [
        "audit.view" => "audit.view",
        "profile.update" => "profile.update",
        "analytics.view" => "analytics.view",
        "integrity_widget.view" => "integrity_widget.view",
        "users.view" => "users.view",
        "users.create" => "users.create",
        "users.edit" => "users.edit",
        "roles.view" => "roles.view",
        "roles.manage" => "roles.manage",
        "permissions.view" => "permissions.view",
        "permissions.manage" => "permissions.manage",
        "settings.view" => "settings.view",
        "settings.manage" => "settings.manage",
        "students.view" => "students.view",
        "students.create" => "students.create",
        "students.edit" => "students.edit",
        "students.import" => "students.import",
        "students.export" => "students.export",
        "instructors.view" => "instructors.view",
        "instructors.create" => "instructors.create",
        "instructors.edit" => "instructors.edit",
        "instructors.delete" => "instructors.delete",
        "groups.view" => "groups.view",
        "groups.create" => "groups.create",
        "groups.edit" => "groups.edit",
        "groups.delete" => "groups.delete",
        "lectures.view" => "lectures.view",
        "lectures.create" => "lectures.create",
        "lectures.edit" => "lectures.edit",
        "lectures.delete" => "lectures.delete",
        "attendance.manage" => "attendance.manage",
        "attendance.report" => "attendance.report",
        "programs.view" => "programs.view",
        "programs.manage" => "programs.manage",
        "tracks.view" => "tracks.view",
        "tracks.manage" => "tracks.manage",
        "job_profiles.view" => "job_profiles.view",
        "job_profiles.manage" => "job_profiles.manage",
        "campus_structure.manage" => "campus_structure.manage",
        "evaluations.manage" => "evaluations.manage",
        "evaluation_results.view" => "evaluation_results.view",
        "users.delete" => "users.delete",
        "tickets.view" => "tickets.view",
        "tickets.create" => "tickets.create",
        "tickets.manage" => "tickets.manage",
        "tickets.lookups" => "tickets.lookups",
        "tickets.settings" => "tickets.settings",
        "tickets.routing" => "tickets.routing",
        "ticket_templates.manage" => "ticket_templates.manage",
        "students.delete" => "students.delete",
        "instructors.import" => "instructors.import",
        "instructors.export" => "instructors.export",
        "groups.import" => "groups.import",
        "groups.export" => "groups.export",
        "lectures.manage" => "lectures.manage",
        "ticket_stages.manage" => "ticket_stages.manage",
        "ticket_categories.manage" => "ticket_categories.manage",
        "ticket_complaints.manage" => "ticket_complaints.manage",
        "ticket_statuses.manage" => "ticket_statuses.manage",
        "ticket_priorities.manage" => "ticket_priorities.manage",
        "ticket_groups.manage" => "ticket_groups.manage",
        "notifications.view" => "notifications.view"
    ];

    public function up(): void
    {
        DB::transaction(function () {
            foreach ($this->mapping as $oldName => $newName) {
                // Check for duplicates before updating
                if (DB::table('permissions')->where('name', $newName)->exists()) {
                    // If target already exists, maybe merge?
                    // But here we expect unique migration.
                    // Let's just update where old exists.
                    continue;
                }

                DB::table('permissions')
                    ->where('name', $oldName)
                    ->update(['name' => $newName]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::transaction(function () {
            foreach ($this->mapping as $oldName => $newName) {
                DB::table('permissions')
                    ->where('name', $newName)
                    ->update(['name' => $oldName]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

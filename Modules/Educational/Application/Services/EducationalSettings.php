<?php

namespace Modules\Educational\Application\Services;

use Modules\Core\Application\Services\SettingsService;
use Modules\Core\Application\Services\AuditLoggerService;

class EducationalSettings
{
    public function __construct()
    {
    }

    // ─── Attendance Settings ─────────────────────────────────────────────────

    /**
     * Get the number of hours after a lecture ends where attendance modifications
     * are still allowed. After this, attendance is automatically locked.
     */
    public function attendanceLockHours(): int
    {
        return (int) get_setting('educational_attendance_lock_hours', 24);
    }

    // ─── Track Settings ──────────────────────────────────────────────────────

    public function trackResponsibleRoles(): array
    {
        $roles = get_setting('educational_track_responsible_roles', []);
        return is_string($roles) ? json_decode($roles, true) ?? [] : (is_array($roles) ? $roles : []);
    }

    // ─── Lectures Settings ───────────────────────────────────────────────────

    public function defaultDisplayedPrograms(): array
    {
        $programs = get_setting('educational_default_displayed_programs', []);
        return is_string($programs) ? json_decode($programs, true) ?? [] : (is_array($programs) ? $programs : []);
    }

    public function defaultDisplayedTracks(): array
    {
        $tracks = get_setting('educational_default_displayed_tracks', []);
        return is_string($tracks) ? json_decode($tracks, true) ?? [] : (is_array($tracks) ? $tracks : []);
    }

    public function supervisorRoles(): array
    {
        $roles = get_setting('educational_supervisor_roles', []);
        return is_string($roles) ? json_decode($roles, true) ?? [] : (is_array($roles) ? $roles : []);
    }

    public function globalSupervisorRoles(): array
    {
        $roles = get_setting('educational_global_supervisor_roles', []);
        return is_string($roles) ? json_decode($roles, true) ?? [] : (is_array($roles) ? $roles : []);
    }
}

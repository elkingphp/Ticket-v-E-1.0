<?php

namespace Modules\Core\Application\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Core\Domain\Models\AuditLog;
use Throwable;

class AuditLoggerService
{
    /**
     * Log a custom event.
     */
    public function log(string $event, string $category, array $old = null, array $new = null, string $level = 'info'): void
    {
        try {
            $data = [
                'user_id' => Auth::id(),
                'event' => $event,
                'category' => $category,
                'old_values' => $old,
                'new_values' => $new,
                'log_level' => $level,
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            if ($level === 'critical') {
                $this->saveSync($data);
            } else {
                $this->saveAsync($data);
            }
        } catch (Throwable $e) {
            $this->fallback($e, $data ?? []);
        }
    }

    protected function saveSync(array $data): void
    {
        $auditLog = AuditLog::create($data);

        // Dispatch event for notification system
        event(new \Modules\Core\Domain\Events\AuditLogCreated($auditLog));
    }

    protected function saveAsync(array $data): void
    {
        // For now, let's keep it sync until we have a proper queue driver configured.
        // In a production app, we would use a Job here.
        $auditLog = AuditLog::create($data);

        // Dispatch event for notification system
        event(new \Modules\Core\Domain\Events\AuditLogCreated($auditLog));
    }

    protected function fallback(Throwable $e, array $data): void
    {
        Log::channel('daily')->error('Audit Logging Failed: ' . $e->getMessage(), [
            'original_data' => $data,
            'exception' => $e
        ]);
    }
}
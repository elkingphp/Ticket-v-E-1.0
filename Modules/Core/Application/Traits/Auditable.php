<?php

namespace Modules\Core\Application\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Domain\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            $model->auditEvent('created');
        });

        static::updated(function (Model $model) {
            $model->auditEvent('updated');
        });

        static::deleted(function (Model $model) {
            $model->auditEvent('deleted');
        });
    }

    protected function auditEvent(string $event)
    {
        if (isset($this->isAuditEnabled) && !$this->isAuditEnabled) {
            return;
        }

        $oldValues = null;
        $newValues = null;

        if ($event === 'updated') {
            $newValues = $this->getChanges();
            $oldValues = array_intersect_key($this->getOriginal(), $newValues);

            // Remove skipped fields
            $skipped = property_exists($this, 'auditSkipped') ? $this->auditSkipped : ['password', 'remember_token'];
            foreach ($skipped as $field) {
                unset($oldValues[$field], $newValues[$field]);
            }

            if (empty($newValues)) {
                return;
            }
        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
            $skipped = property_exists($this, 'auditSkipped') ? $this->auditSkipped : ['password', 'remember_token'];
            foreach ($skipped as $field) {
                unset($newValues[$field]);
            }
        } elseif ($event === 'deleted') {
            $oldValues = $this->getOriginal();
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'category' => $this->getAuditCategory(),
            'log_level' => $this->getAuditLogLevel($event),
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function getAuditCategory(): string
    {
        if (property_exists($this, 'auditCategory')) {
            return $this->auditCategory;
        }

        $className = class_basename($this);
        return str_replace('Module', '', $className);
    }

    protected function getAuditLogLevel(string $event): string
    {
        if (property_exists($this, 'auditLogLevels') && isset($this->auditLogLevels[$event])) {
            return $this->auditLogLevels[$event];
        }

        return $event === 'deleted' ? 'warning' : 'info';
    }
}
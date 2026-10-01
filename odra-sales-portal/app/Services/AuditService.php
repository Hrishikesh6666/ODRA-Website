<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event.
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $notes = null,
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 500),
            'session_id' => session()->getId(),
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Convenience helper for model changes
     */
    public static function logModelChange(string $action, Model $model, ?array $oldValue = null, ?string $notes = null): AuditLog
    {
        return self::log(
            action: $action,
            entityType: class_basename($model),
            entityId: $model->getKey(),
            oldValue: $oldValue,
            newValue: $model->toArray(),
            notes: $notes
        );
    }
}

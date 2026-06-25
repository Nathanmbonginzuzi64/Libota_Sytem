<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogger
{
    public static function log(
        ?User $user,
        string $action,
        string $entityType,
        ?int $entityId,
        string $title,
        ?string $description = null,
        ?array $metadata = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}

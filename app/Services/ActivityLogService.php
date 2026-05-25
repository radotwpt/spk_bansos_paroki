<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public function log(string $action, ?string $modelType = null, ?int $modelId = null, ?int $userId = null, array $meta = []): ActivityLog
    {
        return ActivityLog::create([
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'user_id' => $userId,
            'meta' => $meta,
        ]);
    }
}
